<?php

namespace App\Controllers;

class Media extends BaseController
{
    protected ?string $workspaceRequired = null;

    private const ALBUM_TYPES = ['handover', 'return', 'condition', 'before_after', 'general'];

    public function index()
    {
        if (!$this->db->tableExists('media_albums')) {
            return view('media/album', $this->viewData(['title' => 'Media Albums', 'albums' => [], 'migrationRequired' => true]));
        }

        $q = $this->db->table('media_albums ma')
            ->select('ma.*, COUNT(mi.id) AS item_count')
            ->join('media_items mi', 'mi.album_id = ma.id', 'left')
            ->where('ma.deleted_at', null)
            ->groupBy('ma.id')
            ->orderBy('ma.created_at', 'DESC');

        $module = $this->request->getGet('module') ?? '';
        $refId  = (int) ($this->request->getGet('ref_id') ?? 0);
        if ($module !== '') $q->where('ma.module', $module);
        if ($refId  > 0)    $q->where('ma.ref_id', $refId);

        $albums = $q->get()->getResultArray();

        return view('media/album', $this->viewData([
            'title'  => 'Media Albums',
            'albums' => $albums,
        ]));
    }

    public function createAlbum()
    {
        $rules = [
            'title'      => 'required|min_length[2]|max_length[200]',
            'module'     => 'required|max_length[50]',
            'ref_id'     => 'required|is_natural_no_zero',
            'album_type' => 'required',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $this->db->table('media_albums')->insert([
            'module'     => esc($post['module']),
            'ref_id'     => (int) $post['ref_id'],
            'title'      => esc($post['title']),
            'album_type' => $post['album_type'],
            'is_locked'  => 0,
            'created_by' => (int) session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $albumId = (int) $this->db->insertID();
        $this->logActivity('create', 'media_albums', $albumId, 'Album: '.$post['title']);

        return redirect()->to(base_url('media/albums/'.$albumId))->with('success', 'Album created.');
    }

    public function viewAlbum(int $albumId)
    {
        $album = $this->fetchAlbum($albumId);
        if (!$album) return redirect()->to(base_url('media'))->with('error', 'Album not found.');

        $items = $this->db->table('media_items')
            ->where('album_id', $albumId)
            ->orderBy('sort_order')
            ->get()->getResultArray();

        return view('media/album', $this->viewData([
            'title'  => 'Album: '.esc($album['title']),
            'album'  => $album,
            'items'  => $items,
            'albums' => [],
        ]));
    }

    public function uploadItems(int $albumId)
    {
        $album = $this->fetchAlbum($albumId);
        if (!$album) return redirect()->to(base_url('media'))->with('error', 'Album not found.');
        if ((int) $album['is_locked']) return redirect()->back()->with('error', 'Album is locked.');

        $files   = $this->request->getFiles();
        $uploads = $files['media_files'] ?? [];
        if (!is_array($uploads)) $uploads = [$uploads];

        $dir = WRITEPATH . 'uploads/media/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $captions     = $this->request->getPost('captions') ?? [];
        $conditionTags= $this->request->getPost('condition_tags') ?? [];
        $sort         = (int) $this->db->table('media_items')->where('album_id', $albumId)->countAllResults();

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        foreach ($uploads as $idx => $file) {
            if (!$file || !$file->isValid() || $file->hasMoved()) continue;
            if (!in_array($file->getMimeType(), $allowed)) continue;
            if ($file->getSize() > 10 * 1024 * 1024) continue;

            $name = 'media_'.$albumId.'_'.time().'_'.$idx.'.'.$file->getExtension();
            $file->move($dir, $name);
            $this->db->table('media_items')->insert([
                'album_id'      => $albumId,
                'file_path'     => 'uploads/media/'.$name,
                'caption'       => esc($captions[$idx] ?? ''),
                'condition_tag' => esc($conditionTags[$idx] ?? ''),
                'sort_order'    => $sort++,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to(base_url('media/albums/'.$albumId))->with('success', 'Files uploaded.');
    }

    public function lockAlbum(int $albumId)
    {
        $album = $this->fetchAlbum($albumId);
        if (!$album) return redirect()->to(base_url('media'))->with('error', 'Album not found.');

        $signaturePath = null;
        $file = $this->request->getFile('signature_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dir = WRITEPATH . 'uploads/signatures/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $name = 'sig_'.$albumId.'_'.time().'.'.$file->getExtension();
            $file->move($dir, $name);
            $signaturePath = 'uploads/signatures/'.$name;
        }

        $this->db->table('media_albums')->where('id', $albumId)->update([
            'is_locked'      => 1,
            'signature_path' => $signaturePath,
            'locked_at'      => date('Y-m-d H:i:s'),
            'locked_by'      => (int) session()->get('user_id'),
        ]);

        $this->logActivity('lock', 'media_albums', $albumId);

        return redirect()->to(base_url('media/albums/'.$albumId))->with('success', 'Album locked and signed.');
    }

    public function panel(string $module, int $refId)
    {
        $albums = [];
        if ($this->db->tableExists('media_albums')) {
            $albums = $this->db->table('media_albums ma')
                ->select('ma.*, COUNT(mi.id) AS item_count')
                ->join('media_items mi', 'mi.album_id = ma.id', 'left')
                ->where('ma.module', $module)
                ->where('ma.ref_id', $refId)
                ->where('ma.deleted_at', null)
                ->groupBy('ma.id')
                ->orderBy('ma.created_at', 'DESC')
                ->get()->getResultArray();
        }

        return view('media/panel', $this->viewData([
            'title'       => 'Media',
            'albums'      => $albums,
            'module'      => $module,
            'refId'       => $refId,
            'albumTypes'  => self::ALBUM_TYPES,
        ]));
    }

    private function fetchAlbum(int $id): ?array
    {
        if (!$this->db->tableExists('media_albums')) return null;
        $row = $this->db->table('media_albums')->where('id', $id)->where('deleted_at', null)->get()->getRowArray();
        return $row ?: null;
    }
}
