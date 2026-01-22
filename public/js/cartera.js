const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

const CARTERA = {
  all: function(){
    // Usar cod_alumno si está disponible para vista unificada
    var cod_alumno = $("#cod_alumno").val();
    var id_cost = $("#id_cost").val();

    var data = {};
    if (cod_alumno) {
        data.cod_alumno = cod_alumno;
    } else if (id_cost) {
        data.id = id_cost;
    }

    if($("#purseAll").length > 0){
      data = $("#purseAll").serialize();
      // Si el serialize no incluye cod_alumno y lo tenemos, agregarlo
      if (cod_alumno && data.indexOf('cod_alumno') === -1) {
          data += '&cod_alumno=' + cod_alumno;
      }
    } else if($("#FormRequestOtros").length > 0){
      data = $("#FormRequestOtros").serialize();
    }
    return AJAX("/purse/all",'POST', data);
  },
  edit:function(){
    return AJAX("/purse/edit",'POST',$("#FormPurseEdit").serialize());
  },
  history:function(){
    return AJAX("/history/search",'POST',$("#FormPurseHistory1").serialize());
  },
  delete:function(){
    return AJAX("/history/delete",'POST',$("#ModalPasswordAdmin").serialize());
  },
  suma:function(){
    // Usar id_cost directamente si existe
    var id_cost = $("#id_cost").val();
    var data = { id: id_cost };
    if($("#FormRequestOtros").length > 0){
      data = $("#FormRequestOtros").serialize();
    }
    return AJAX("/purse/total",'POST', data);
  },
  totales:function(){
    // Nuevo método para obtener todos los totales
    var id_cost = $("#id_cost").val();
    var cod_alumno = $("#cod_alumno").val();
    return AJAX("/purse/totales",'POST', { id: id_cost, cod_alumno: cod_alumno });
  }
}

const ENTRY = {
  all:function(){
    // Usar id_cost directamente si existe
    var id_cost = $("#id_cost").val();
    var data = { id: id_cost };
    if($("#FormRequestOtros").length > 0 && $("#FormRequestOtros input[name='id']").val()){
      data = $("#FormRequestOtros").serialize();
    }
    console.log("ENTRY.all: Enviando datos:", data);
    return AJAX("/entry/all",'POST', data);
  },
  get:function(id){
    return AJAX("/entry/get/"+id,'GET', {});
  },
  create:function(){
    return this.createForm("#formEntry");
  },
  createForm:function(nameForm){
    return AJAX("/entry/store",'POST',$(nameForm).serialize());
  },
  updateForm:function(id, nameForm){
    return AJAX("/entry/update/"+id,'POST',$(nameForm).serialize());
  }
}

const OtherENTRIES = {
  all:function(){
    // Usar id_cost directamente si existe
    var id_cost = $("#id_cost").val();
    var data = { id: id_cost };
    if($("#FormRequestOtros").length > 0 && $("#FormRequestOtros input[name='id']").val()){
      data = $("#FormRequestOtros").serialize();
    }
    console.log("OtherENTRIES.all: Enviando datos:", data);
    return AJAX("/other/all",'POST', data);
  },
  get:function(id){
    return AJAX("/other/entry/get/"+id,'GET', {});
  },
  create:function(){
    return this.createForm("#formEntry1");
  },
  createForm:function(nameForm){
    return AJAX("/other/entry/store",'POST',$(nameForm).serialize());
  },
  updateForm:function(id, nameForm){
    return AJAX("/other/entry/update/"+id,'POST',$(nameForm).serialize());
  }
}

const thirdEntry = {
  add:function(){
    return AJAX("/third/entry/add", 'POST', $("#addThirdEntry").serialize());
  },
  addActivity:function(){
    return AJAX("/third/activity/add",'POST',$("#formAddThirdActivity").serialize());
  },
  listActivity:function(){
    return AJAX("/third/activity/",'GET',$("#formAddThirdActivity").serialize());
  },
  search:function(name){
    return AJAX("/third/search/"+name, 'GET', { id: '1'});
  }

}

const ESTUDIANTE = {
  search:function(name){
    return AJAX("/student/search/"+name, 'GET', {});
  },
  searchAll:function(name){
    return AJAX("/student/search/all/"+name, 'GET', {});
  }
}

const Synchronization = {
  count:function(){
    return AJAX("/synchronization/count/local-cloud",'GET',{});
  }
}


const AJAX =  function(url,method,data){
  // Obtener token CSRF del meta tag
  var token = $('meta[name="csrf-token"]').attr('content');
  
  // Si data es un objeto, agregar el token
  if(typeof data === 'object' && !(data instanceof FormData)){
    data._token = token;
  } else if(typeof data === 'string'){
    // Si es un string serializado, agregar el token
    if(data.indexOf('_token=') === -1){
      data += (data ? '&' : '') + '_token=' + encodeURIComponent(token);
    }
  }
  
  return $.ajax({
    url: url,
    method: method,
    data: data,
    headers: {
      'X-CSRF-TOKEN': token
    }
  });
}

const ApppendTo = function (hijos,padre){
  for (let index = 0; index < hijos.length; index++) {
    hijos[index].appendTo(padre);
  }
}

const BucarIndice  = function (nombre){
  var fecha = 0;
  for (let index = 0; index < MESES.length; index++) {
    if(MESES[index] == nombre){
      fecha = index+1;
      if(fecha < 10){
        fecha = "0"+fecha;
      }
      return fecha;
    }
  }
}
