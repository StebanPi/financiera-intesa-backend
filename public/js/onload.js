const Sincronizacion = Synchronization.count();
Sincronizacion.done(function(response){
    var res = JSON.parse(response);
    console.log(res);
});