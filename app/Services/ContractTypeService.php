<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ContractTypeService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @return list<array<string,mixed>> */
    public function activeTypes(): array
    {
        if (! $this->db->tableExists('contract_types')) {
            return $this->fallbackTypes();
        }

        $this->ensureSeeded();

        return $this->db->table('contract_types')
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name_en', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    public function allTypes(): array
    {
        if (! $this->db->tableExists('contract_types')) {
            return $this->fallbackTypes();
        }

        $this->ensureSeeded();

        return $this->db->table('contract_types')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name_en', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function findBySlug(string $slug): ?array
    {
        if ($slug === '' || ! $this->db->tableExists('contract_types')) {
            return null;
        }

        $this->ensureSeeded();

        return $this->db->table('contract_types')->where('slug', $slug)->get()->getRowArray() ?: null;
    }

    public function findById(int $id): ?array
    {
        if ($id < 1 || ! $this->db->tableExists('contract_types')) {
            return null;
        }

        return $this->db->table('contract_types')->where('id', $id)->get()->getRowArray() ?: null;
    }

    /** @param array<string,mixed> $post */
    public function saveFromPost(array $post, int $id = 0): int
    {
        $slug = strtolower(trim((string) ($post['slug'] ?? '')));
        $slug = preg_replace('/[^a-z0-9_\-]+/', '_', $slug) ?? 'other';
        $data = [
            'slug'       => $slug,
            'name_en'    => esc(trim((string) ($post['name_en'] ?? ''))),
            'name_ar'    => esc(trim((string) ($post['name_ar'] ?? ''))) ?: null,
            'is_active'  => ! empty($post['is_active']) ? 1 : 0,
            'sort_order' => (int) ($post['sort_order'] ?? 99),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id > 0) {
            $existing = $this->findById($id);
            if ($existing && ! empty($existing['is_system'])) {
                unset($data['slug']);
            }
            $this->db->table('contract_types')->where('id', $id)->update($data);

            return $id;
        }

        $data['is_system']  = 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->table('contract_types')->insert($data);

        return (int) $this->db->insertID();
    }

    public function resolveTypeForContract(?int $typeId, ?string $slug, ?string $unitType = null): array
    {
        if ($typeId > 0) {
            $row = $this->findById($typeId);
            if ($row) {
                return $row;
            }
        }

        if ($slug !== null && $slug !== '') {
            $row = $this->findBySlug($slug);
            if ($row) {
                return $row;
            }
        }

        if (strtolower((string) $unitType) === 'parking') {
            return $this->findBySlug('parking') ?? ['id' => 0, 'slug' => 'parking', 'name_en' => 'Parking Contract'];
        }

        return $this->findBySlug('residential') ?? ['id' => 0, 'slug' => 'residential', 'name_en' => 'Residential Contract'];
    }

    /** @return list<array<string,mixed>> */
    private function fallbackTypes(): array
    {
        return [
            ['id' => 0, 'slug' => 'parking', 'name_en' => 'Parking Contract', 'name_ar' => 'عقد موقف', 'is_system' => 1],
            ['id' => 0, 'slug' => 'residential', 'name_en' => 'Residential Contract', 'name_ar' => 'عقد سكني', 'is_system' => 1],
            ['id' => 0, 'slug' => 'commercial', 'name_en' => 'Commercial Contract', 'name_ar' => 'عقد تجاري', 'is_system' => 1],
            ['id' => 0, 'slug' => 'other', 'name_en' => 'Other Contract', 'name_ar' => 'عقد آخر', 'is_system' => 1],
        ];
    }

    private function ensureSeeded(): void
    {
        if ($this->db->table('contract_types')->countAllResults() > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        foreach ($this->fallbackTypes() as $i => $row) {
            $this->db->table('contract_types')->insert([
                'slug'       => $row['slug'],
                'name_en'    => $row['name_en'],
                'name_ar'    => $row['name_ar'] ?? null,
                'is_system'  => 1,
                'is_active'  => 1,
                'sort_order' => $i + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
