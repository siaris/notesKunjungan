<link href="<?= BASEURL?>/assets/css/alt-aris-style.css" rel="stylesheet">
<div class="card" id="identitas-ket-pasien-at-emr">
</div>
<div class="card" id="emrDiv">
	<span id="notes-part" v-bind:class="notesAvailability">
	<div class="bg-info" style="padding:5px;">
		<li class="icon-edit"></li>
		<a style="color:#FFF" @click="notesModal = true" href="" data-toggle="modal">CATATAN KUNJUNGAN PASIEN</a>
		<modal v-if="notesModal" @close="notesModal = false">
			<h3 slot="header">CATATAN KUNJUNGAN <?= '['.$data[0]->no_rm.'] '.$data[0]->nama.' '.$pl ?></h3>
			<ul id="dtNotes">
			</ul>
		</modal>
	</div>
	</span>
	<span>
	<div class="bg-info" style="padding:5px;">
		<li class="icon-list"></li>
		<a style="color:#FFF" @click="notesViewModal = true" href="" data-toggle="modal">LIHAT CATATAN & SOAP</a>
		<modal v-if="notesViewModal" @close="notesViewModal = false">
			<h3 slot="header">CATATAN KUNJUNGAN & SOAP <?= '['.$data[0]->no_rm.'] '.$data[0]->nama.' '.$pl ?></h3>
			<ul id="dtNotesView">
			</ul>
		</modal>
	</div>
	</span>
</div>
<script>
var kategoriTidakReadonly = ['[CATATAN_TBAK]','[MASALAH_TINDAKAN_ICU]'];
$(document).ready(function() {
	
})

const emrApp = new Vue({
	el:'#emrDiv',
	data() {
		return{
			dR: '<?= $detail_reg?>',
			DPJP: '<?= $data[0]->id_dokter?>',
			notesAvailability: 'hide',
			ins: '<?= $data[0]->instalasi_id?>',
			notesModal: false,
			notesViewModal: false,
			noRM: '<?= $data[0]->no_rm?>',
			admSug: ['KTP','KK','KARTU BPJS','KARTU JAMINAN LAIN','HASIL SWAB DARI LUAR','SURAT RUJUKAN','SURAT ISOLASI MANDIRI','SURAT PERNYATAAN BEBAS BIAYA','INFORMED CONSENT','GENERAL CONSENT','EDUKASI PASIEN','LAIN-LAIN','CATATAN_TBAK','MASALAH_TINDAKAN_ICU']
		}
	},
	mounted: function(){
		this.initResume()
		this.initEMRAvailable()
	},
	methods:{
		async initResume(){
			let tX = `<div class="bg-info" style="padding:5px;">
					<li class="icon-edit"></li>
					<a style="color:#FFF"  onclick="PopupCenter('/medical_record/catatan_medis/cek_resume/`+this.dR+`/','Create E-Resume', 1000, 700)" href="" data-toggle="modal">PROSES E RESUME</a>
				</div>`
			if(PrimaSession.pegawai_id == this.DPJP){
				$('#resume-part').html(tX)
				this.initNotes()
				return
			}else{
				this.getAvailabilityResume(tX)
			}
			
			return
		},
		async getAvailabilityResume(t){
			let dr= this.dR
			await $.ajax({
				url: BASEURL +'/medical_record/act_resume/check_availability/'+dr+'/',dataType: "json",type: "GET"
			})
			.done(
				function(d){
					if(d.s == '1'){
						$('#resume-part').html(t)
						emrApp.notesAvailability = 'block'
					}
				}
			)
			.fail(()=>{
				console.log('gagal callback ke server')	
			}) 
		},
		initNotes(){
			$.ajax({
				url: BASEURL +'/medical_record/act_resume/check_availability/'+this.dR+'/',dataType: "json",type: "GET"
			})
			.done(
				function(d){
					if(d.s == '1')
						emrApp.notesAvailability = 'block'
				}
			)
			.fail(()=>{
				console.log('gagal callback ke server')	
			}) 
			return
		},
		async callNote(){
			$('div#modal-list-perjanjian #recent-notes').html('')
			await $.ajax({
					url: BASEURL +'/medical_record/emr/show_notes/notesmedis/'+this.dR+'/',dataType: "json",type: "GET"
				})
				.done(
					function(d){
						let L = []
						if(d.result.length > 0){
							for(n of d.result){
								Ig = (n.PImg != '')?'<br><img style="width:30%;cursor:pointer;" onclick="javascript:$.facebox(\'<span>tekan escape untuk kembali</span><br><iframe src=&quot;'+ROOTURL+n.PImg+'&quot; title=&quot;description&quot; height=&quot;800&quot; width=&quot;800&quot;></iframe>\')" src="'+ROOTURL+n.PImg+'" />':''
								L.push(n.D+' . '+n.Un+' : '+n.Tx+Ig)
							}
							$('div#modal-list-perjanjian #recent-notes').html(implode('<br>',L))
						}
					}
				)
				.fail(()=>{
					alert('gagal dapatkan catatan')
				})
		},
		async callAllNote(){
			$('div.modal-container .modal-header,div.modal-container .modal-body').html('')
			await $.ajax({
					url: BASEURL +'/medical_record/emr/show_all_notes/'+this.noRM+'/',dataType: "json",type: "GET"
				})
				.done(
					function(d){
						let L = []
						if(d.result.length > 0){
							for(n of d.result){
								Ig = (n.PImg != '')?'<br><img style="width:30%;cursor:pointer;" onclick="javascript:$.facebox(\'<span>tekan escape untuk kembali</span><br><iframe src=&quot;'+ROOTURL+n.PImg+'&quot; title=&quot;description&quot; height=&quot;800&quot; width=&quot;800&quot;></iframe>\')" src="'+ROOTURL+n.PImg+'" />':''
								L.push(n.D+' . '+n.Un+' : '+n.Tx+Ig)
							}
							$('div#modal-list-perjanjian .modal-body').html(implode('<br>',L))
						}
					}
				)
				.fail(()=>{
					alert('gagal dapatkan catatan semua kunjungan')
				})
		},
		async initEMRAvailable(){
			$.ajax({
				url: BASEURL +'/medical_record/emr_generator/get_mr/'+this.dR+'/',dataType: "json",type: "GET"
			})
			.done(
				function(D){
					if(D !== null)
						for(i in D){
							t = `<div class="bg-info" style="padding:5px;">
					<li class="icon-edit"></li>
					<a style="color:#FFF" onclick="PopupCenter('`+D[i][1]+`','', 1000, 700)" href="" data-toggle="modal">`+D[i][0]+`</a>
				</div>`
							$('#EMR-Part').append(t)
						}
				}
			)
			.fail(()=>{
				console.log('gagal callback ke server')	
			}) 
			return
		},
		callNoteSoap(){
			console.info('callNoteSoap')
			$('div#modal-list-perjanjian #recent-notes').html('')
			var Fo = new FormData()
			Fo.append('R', emrApp.noRM)
			Fo.append('D', emrApp.dR)
			$.ajax({
					url: BASEURL +'/medical_record/soap_base/soap_notes/'+this.dR+'/',type: "POST",data: Fo,processData: false,contentType: false,dataType: "json"
				})
				.done(
					function(d){
						emrApp.build_note_soap(d)
					}
				)
				.fail(()=>{
					alert('gagal dapatkan catatan')
				})
			return
		},
		callAllNoteSoap(){
			console.info('callAllNoteSoap')
			$('div#modal-list-perjanjian #recent-notes').html('')
			var Fo = new FormData()
			Fo.append('R', emrApp.noRM)
			Fo.append('D', emrApp.dR)
			$.ajax({
					url: BASEURL +'/medical_record/soap_base/soap_notes/ALL/',type: "POST",data: Fo,processData: false,contentType: false,dataType: "json"
				})
				.done(
					function(d){
						emrApp.build_note_soap(d)
					}
				)
				.fail(()=>{
					alert('gagal dapatkan catatan')
				})
			return
		},
		build_note_soap(D){
			T = '';
			T += '<table class="table table-striped table-bordered">';
			for(i of D.soap.reverse()){
				T+= `<tbody class='`+i.id+`'><tr><td rowspan="4">`+i.kunj+`<br>`+i.editor.replace(/\:[0-5][0-9]$/g,'')+`</td><td>S : `+i.subjektif.replace(/\n/g, "<br />")+`</td></tr><tr><td>O : `+i.objektif.replace(/\n/g, "<br />")+`</td></tr><tr><td>A : `+i.assesment.replace(/\n/g, "<br />")+`</td></tr><tr><td>P : `+i.planning.replace(/\n/g, "<br />")+`</td></tr></tbody>`
			}
			T += '</table>'
			$('div#modal-list-perjanjian #recent-notes').append(T)
			return
		}
	}
})

const modal_notes = Vue.component('modal', {
  template: '#modal-notes',
  data() {
	return{
			dR: '<?= $detail_reg?>',
			notesViewModal: emrApp.notesViewModal
		}
	},
	mounted: function() {
		this.initModal()
		return
	},
	methods:{
		initModal(){
			if(this.notesViewModal === true){
				emrApp.callNoteSoap()
				this.initOpenNoteSoapAll()
			}else{
				emrApp.callNote()
				this.initTypeKetForAdm()
				this.initSimpan()
				this.initOpenAll()
			}
			return
		},
		initTypeKetForAdm(){
			var charTypeaheadTriggered = '\n'
			var sr = emrApp.admSug.map(v=> '['+v+']' )
			$('div#modal-list-perjanjian #text_notes').typeahead({
				source: sr,
				matcher: function (item) {
					var last = this.query.split(charTypeaheadTriggered);
					this.query = $.trim(last[last.length-1]);
					if(this.query.length) return ~item.toLowerCase().indexOf(this.query.toLowerCase())
				},
				updater: function(obj_r) {
					value_this = this.$element.val();
					return value_this.replace(this.query,'')+obj_r;
				},
				select: function () {
					var val = JSON.parse(this.$menu.find('.active').attr('data-value')), text
					if (!this.strings) text = val[this.options.property] 
					else text = this.updater(val);
					this.$element.val(text)
					console.log(text)
					if(kategoriTidakReadonly.indexOf(text) == -1)
					  $('div#modal-list-perjanjian #text_notes').attr("readonly", true);
					else{
						var t_tmp = '';
						if(text == '[MASALAH_TINDAKAN_ICU]'){
							var t_tmp = '\nMASALAH : \nTINDAKAN : ';
						}

					  $('div#modal-list-perjanjian #text_notes').val(text+' : '+t_tmp);
					}
					
					return this.hide()
				},
				onselect: function(obj) {
					// if(kategoriTidakReadonly.indexOf(obj) == -1)
					//  $('div#modal-list-perjanjian #text_notes').attr("readonly", true)
					// else
					//  $('div#modal-list-perjanjian #text_notes').val(obj+' : ');

					$('div#modal-list-perjanjian #text_notes').attr("readonly", true)
				}
			})
		},
		initSimpan(){
			$('div#modal-list-perjanjian input#do-simpan').on('click',function(e){
				e.preventDefault()
				if($('div#modal-list-perjanjian #text_notes').val() == '')
					return
				var F = new FormData()
				F.append('file', $('form#data-notes input[type=file]')[0].files[0])
				F.append('t', $('div#modal-list-perjanjian #text_notes').val())
				F.append('dR', emrApp.dR)
				
				$('div#modal-list-perjanjian input#do-simpan').addClass('disabled')
				$.ajax({
					url: BASEURL +'/medical_record/emr/save_notes/',
					type: "POST",
					data: F,
					processData: false,
					contentType: false
				})
				.done(
					function(d){
						emrApp.callNote()
						//clear textarea
						$('div#modal-list-perjanjian #text_notes').val('')
						$('form#data-notes input[type=file]').val('')
						//enable tombol save
						$('div#modal-list-perjanjian input#do-simpan').removeClass('disabled')
					}
				)
				.fail(()=>{
					alert('gagal simpan catatan')
					$('div#modal-list-perjanjian input#do-simpan').removeClass('disabled')
				})
			})
		},
		initOpenAll(){
			$('div#modal-list-perjanjian input#openAllDoc').on('click',function(e){
				$('div.modal-container .modal-header,div.modal-container .modal-body').html('')
				$(this).addClass('disabled')
				emrApp.callAllNote()
			})
		},
		initOpenNoteSoapAll(){
			$('div#modal-list-perjanjian input#openAllDoc').on('click',function(e){
				// $('div.modal-container .modal-header,div.modal-container .modal-body').html('')
				$(this).addClass('disabled')
				emrApp.callAllNoteSoap()
			})
		}
	}
})
</script>

<script type="text/x-template" id="modal-notes">
  <transition name="modal">
    <div class="modal-mask" id="modal-list-perjanjian">
      <div class="modal-wrapper">
        <div class="modal-container">

          <div class="modal-header">
            <slot name="header">
              default header
            </slot>
          </div>
          <div class="modal-body" style="padding:0px; color: black;min-height: 200px;">
            <slot name="body">
			<h5>LIST CATATAN</h5>
			<div id="recent-notes"></div>
			<form id="data-notes" method="post" enctype="multipart/form-data" v-bind:class="(notesViewModal === true)?'hide':'block'">
			<h5>FORM</h5>
			<div id="form-group form-notes">
					<div class="col-sm-11">
						<textarea class="form-control" id="text_notes"></textarea>
					</div>
					<div class="col-sm-11">
						<input id="upload-img" name="img" type="file" />
					</div>
					<div class="col-sm-1">
						<input type="button" value="simpan" id="do-simpan" class="btn btn-success">
					</div>
			</div>
			</form>
            </slot>
		</div>
          <div class="modal-footer">
            <slot name="footer">
			<input type="button" id="openAllDoc" class="btn btn-success" value="BUKA CATATAN MR INI">
			  <input type="button" id="close" class="btn btn-danger" @click="$emit('close')" value="TUTUP">
            </slot>
          </div>
        </div>
      </div>
    </div>
    </div>
  </transition>
</script>