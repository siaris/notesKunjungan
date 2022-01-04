<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH."models/pustakatabelmodel.php";

class MrNotesModel extends PustakaTabelModel {
	public function __construct() {
        parent::inisiasi_tabel();
		$this->tableName = 'mr_notes';
		$this->primaryKey = 'detail_reg';
		$this->resultMode = 'array';
		$this->eksis_notes = [];
    }
	
	public function saveEprescription($key,$data){
		$this->_checkEksis($data['detail_reg']);
		$new_node = ['_no_eresep'=>$key,'id_dokter'=>$data['kode_dokter'],'alergi'=>$data['alergi'],'berat_badan'=>$data['berat_badan'],'tinggi_badan'=>$data['tinggi_badan'],'sCr'=>$data['status_resep_ini'],'oFreeText'=>$data['oFT']];
		if(isset($data['kode_dokter_konsulen'])){
			$new_node['id_dokter_konsulen'] = $data['kode_dokter_konsulen'];
			if(preg_match('/^(((P|p){2})|((D|d)[0-9]))/',$this->session->userdata['userLogin']['nip'])){
				$new_node['exPegN'] = $this->session->userdata['userLogin']['name'];
				$new_node['exPegID'] = $this->session->userdata['userLogin']['nip'];
			}
		}
		$this->now_notes = $this->eksis_notes;
		$this->now_notes['eprescription'][] = $new_node;
		
		$this->saveAttribute(['berat_badan','tinggi_badan'],$data);
		
		$data_save = ['id'=>$data['detail_reg'],'notes_json'=>json_encode($this->now_notes)];
		// var_dump($data_save);exit;
		$this->saveNotes($data_save);
		return;
	}
	
	public function saveNotesMedis($data){
		$this->_checkEksis($data['detail_reg']);
		$d = $data;
		unset($d['detail_reg']);
		$new_node = $d;
		$this->now_notes = $this->eksis_notes;
		$this->now_notes['notesmedis'][] = $new_node;
		$data_save = ['id'=>$data['detail_reg'],'notes_json'=>json_encode($this->now_notes)];
		$this->saveNotes($data_save);
		return;
	}
	
	public function saveAppend($d){
		$this->_checkEksis($d['detail_reg']);
		$this->now_notes = $this->eksis_notes;
		$id = $d['detail_reg'];
		unset($d['detail_reg']);
		array_walk($d,function($v,$k) use(&$nA){
			if($v <> '') $nA[] = $k;
		});
		if(!empty($nA)){
			$this->saveAttribute($nA,$d);
			$data_save = ['id'=>$id,'notes_json'=>json_encode($this->now_notes)];
			$this->saveNotes($data_save);
		}
		return;
	}
	
	public function saveAttribute($arrKey,$data){
		foreach($arrKey as $keyChange){
			if($data[$keyChange] <> $data['old_'.$keyChange])
				$this->now_notes[$keyChange][] = $data[$keyChange];
		}
		return;
	}
	
	public function checkEksis($detail_reg){
		$this->_checkEksis($detail_reg);
		return $this->eksis_notes;
	}
	
	private function saveNotes($data_save){
		if(empty($this->eksis_notes)){
			$data_save['detail_reg'] = $data_save['id'];
			unset($data_save['id']);
		}
		$this->save($data_save);
		return;
	}
	
	private function _checkEksis($detail_reg){
		$raw_result = $this->queryOne('detail_reg = '.$detail_reg,'notes_json',null);
		$this->eksis_notes = !empty($raw_result)?json_decode($raw_result,true):[];
		// var_dump($this->eksis_notes);
		return;
	}
}?>