<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once "medical_record_controller.php";

class Notes extends Medical_record_controller {
	
	public function __construct() {
        $this->trdParty = ['Date Picker'=>['css/datepicker.css','js/bootstrap-datepicker.js'],'Datetime Picker'=>['css/bootstrap-datetimepicker.css','js/bootstrap-datetimepicker.js'],
		'Facebox'=>['css/facebox.css','js/facebox.js']];
		parent::__construct();
		$this->previewVersion = '';
		$this->kirim_nilai_balik = 'json';
		$this->isSingleThread = true;
	}
	
	protected function init() {
        $this->css_theme_project = array('bootstrap', 'main', 'DT_bootstrap', 'override', 'jquery.slidepanel', 'datepicker', 'style', 'jquery.sliding_menu');
        $this->js_theme_project = array('bootstrap.min', 'jquery.sliding_menu', 'bootstrap-typeahead','jquery.validate.min','moment-with-locales.min', 'bootstrap-datepicker','media/medical_record/medical_record_base');
	}
	
	//halaman
	function save_notes(){
		$this->defPath = '/supports/pasien/00/';
		if($this->input->post()){
			$this->load->model('medical_record/mrnotesmodel');
			$s = $this->input->post();
			// var_dump($s);
			// var_dump($_FILES);exit;
			$P = '';
			if(isset($_FILES['file'])){
				$config['upload_path']   = ROOTPATH.$this->defPath;
				$config['allowed_types'] = 'jpeg|jpg|png|gif|pdf';
				$config['encrypt_name']	 = true;
				$this->load->library('upload',$config);
				
				if($this->upload->do_upload('file')){
					$d = $this->upload->data();
					$P = $this->defPath.$d['file_name'];
				}
			}
			
			$d = ['detail_reg'=>$s['dR'],'Tx'=>$s['t'],'D'=>date('Y-m-d H:i:s'),'Uid'=>$this->session->userdata['userLogin']['id'],'O'=>['st'=>'c'],'PImg'=>$P];
			$this->mrnotesmodel->saveNotesMedis($d);
			echo json_encode(['success'=>true]);
		}
		return;
	}
	
	private function dummyNotes(){
		return [['notes_json'=>json_encode(['notesmedis'=>[["Tx"=> "D1",
		"D" => date('Y-m-d H:i:s'),
		"Uid" => $this->session->userdata['userLogin']['id'],
		"O"=> ['st'=>'c'],
		"PImg"=> "/newserverr/assets/img/rsuppersahabatan.png"]
		]])
		]];
	}

	//halaman
	function show_all_notes($rm,$isRm = '1'){
		$this->load->model(['medical_record/mrnotesmodel','usermodel']);
		if($isRm == '1')
			$N = $this->mrnotesmodel->db->query('select mr_notes.*,poli_kunjungan_pasien.tanggal from mr_notes inner join poli_kunjungan_pasien on id=detail_reg inner join pendaftaran using(no_reg) where notes_json like \'%"notesmedis":%\' and no_rm = '.$rm)->result_array();
		else{
			if($rm != '0')
				$N = $this->mrnotesmodel->db->query('select mr_notes.*,poli_kunjungan_pasien.tanggal from mr_notes inner join poli_kunjungan_pasien on id=detail_reg inner join pendaftaran using(no_reg) where notes_json like \'%"notesmedis":%\' and no_rm = (select no_rm from poli_kunjungan_pasien inner join pendaftaran using(no_reg) where id = '.$rm.')')->result_array();
			else
				$N = $this->dummyNotes();
		}
		$res = [];$k = 'notesmedis'; 
		if(!empty($N)){
			$U = _parseDropdown($this->usermodel->findAll(), $field_value='name', $field_key='id','awal-kosong');
			foreach($N as $n){
				$tempAttr['dt_n'] = $n['detail_reg'];
				$tempAttr['tgl_n'] = $n['tanggal'];
				$rd = json_decode($n['notes_json'],true);	
				if(!empty($rd[$k])){
					array_walk($rd[$k],function($vl,$ky) use(&$res,&$U,&$tempAttr){
						$vl['Un'] = $U[$vl['Uid']];
						$vl['dt_reg_n'] = $tempAttr['dt_n'];
						$vl['tanggal'] = $tempAttr['tgl_n'];
						$res[] = $vl;
						// $res[$ky]['Un'] = $U[$vl['Uid']]; 
					});	
				}	
			}
		}
		if($this->kirim_nilai_balik == 'json'){
			echo json_encode(['success'=>true,'result'=>$res]);
			return;
		}else{
			return $res;
		}
	}

	//halaman
	function show_notes($k,$v){
		$this->load->model(['medical_record/mrnotesmodel','usermodel']);
		$r = $this->mrnotesmodel->queryOne('detail_reg = '.$v,'notes_json',null);
		$rd = (!empty($r))?json_decode($r,true):[];
		$res = [];
		if(!empty($rd) && isset($rd[$k])){
			$U = _parseDropdown($this->usermodel->findAll(), $field_value='name', $field_key='id','awal-kosong');
			array_walk($rd[$k],function($vl,$ky) use(&$res,&$U){
				$res[$ky] = $vl;
				$res[$ky]['Un'] = $U[$vl['Uid']]; 
			});
		}
		echo json_encode(['success'=>true,'result'=>$res]);
		return;
	}


	public function upload_image(){
		$config['upload_path']   = './assets/upload/emr/';
	    $config['allowed_types'] = 'jpeg|jpg|png|gif';
	    $config['encrypt_name']	 = true;
	    $this->load->library('upload',$config);
	    if ( ! $this->upload->do_upload('userfile'))
        {
            $error = array('error' => $this->upload->display_errors());
            print_r($error);
        }
        else
        {
            $data=$this->upload->data();
            echo '{"nm_file":"'.site_url('assets/upload/emr').'/'.$data['file_name'].'"}';
            exit();
        }
	}

    //halaman
	function change_attribute(){
		if($this->input->post()){
			$this->load->model(['medical_record/mrnotesmodel','pasienmodel','pendaftaranmodel']);
			$S = $this->input->post();
			
			$this->pendaftaranmodel->changeResultMode('array');
			$K = $this->pendaftaranmodel->load_data_in_right_column('',$S['_ID'],FALSE);
			$kunj = $K[0];
			
			$this->pasienmodel->save(['id'=>$kunj['no_rm'],'alergi'=>$S['A']]);
			
			$this->mrnotesmodel->saveAppend(['detail_reg'=>$S['_ID'],'berat_badan'=>$S['B'],'tinggi_badan'=>$S['T']]);
			
			echo json_encode(['ok']);
			
		}
		
	}
	
}?>