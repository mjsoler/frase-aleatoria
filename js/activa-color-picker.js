jQuery(document).ready(function($){

    var myOptions = {

    // color por defecto ninguno ya que esta definido en el input en data-default-color
    defaultColor: false,
    // callback cuando se cambie a un color válido, no definida
    change: function(event, ui){},
    // callback cuando el color este vacio o no sea válido, no definida
    clear: function() {},
    // oculta los controles en la carga
    hide: true,
    // muestra un grupo de colores comunes debajo del panel
    palettes: true
};

    $('.my-color-picker').wpColorPicker();
});
