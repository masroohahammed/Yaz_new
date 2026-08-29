<?php
namespace App\Controllers;

class Utility extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    public function index()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $pg = $this->paginate(25);
        $q  = $this->db->table('utility_readings ur')
            ->select('ur.*, f.name as facility_name')
            ->join('facilities f', 'f.id=ur.facility_id', 'left')
            ->where('ur.reading_date >=', $from)
            ->where('ur.reading_date <=', $to);
        $this->scopeFacilities($q, 'ur.facility_id');
        $total     = (clone $q)->countAllResults(false);
        $utilities = $q->orderBy('ur.reading_date', 'DESC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();

        $elecTotal = array_sum(array_column(array_filter($utilities, fn($u) => $u['type']==='electricity'), 'cost'));
        $waterTotal= array_sum(array_column(array_filter($utilities, fn($u) => $u['type']==='water'), 'cost'));

        return view('utility/index', $this->viewData([
            'title'       => 'Utility & Energy',
            'utilities'   => $utilities,
            'facilities'  => $facilities,
            'elecTotal'   => $elecTotal,
            'waterTotal'  => $waterTotal,
            'from'        => $from,
            'to'          => $to,
            'totalCount'  => $total,
            'perPage'     => $pg['perPage'],
            'currentPage' => $pg['page'],
        ]));
    }

    public function create()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('utility/create', $this->viewData(['title'=>'Log Utility Reading','facilities'=>$facilities]));
    }

    public function store()
    {
        $rules = ['facility_id'=>'required','type'=>'required','reading_date'=>'required','units'=>'required|numeric','cost'=>'required|numeric'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());

        $this->db->table('utility_readings')->insert([
            'facility_id'  => $this->request->getPost('facility_id'),
            'type'         => $this->request->getPost('type'),
            'reading_date' => $this->request->getPost('reading_date'),
            'units'        => (float)$this->request->getPost('units'),
            'cost'         => (float)$this->request->getPost('cost'),
            'meter_reading'=> (float)$this->request->getPost('meter_reading'),
            'notes'        => esc($this->request->getPost('notes')),
            'created_by'   => session()->get('user_id'),
        ]);
        return redirect()->to(base_url('utility'))->with('success','Utility reading logged.');
    }
}
