  // Declarações em um namespace não global para evitar conflitos
  var Mateus = {
    map: null,
    geocoder: null,
    mapZoomDefault: 6,
    css3_rotate_prefix: "rotate(",
    css3_rotate_suffix: "deg)",
    theDate: null,
    n_horas: null,
    n_minutos: null,
    n_segundos: null,
    // Helper para definir o CSS dos ponteiros
    setCSSfor: function(jqSeletor, n_valor){
      var rotation = Mateus.createRotateString(n_valor);
      $(jqSeletor).css(
        {
          "-moz-transform" : rotation, 
          "-webkit-transform" : rotation, 
          "-o-transform" : rotation, 
          "-ms-transform" : rotation, 
          "transform" : rotation
        }
      );
    },
    // Helper para criar a instrução de Rotação
    createRotateString: function(n_valor){
      return Mateus.css3_rotate_prefix + n_valor + Mateus.css3_rotate_suffix;
    },
    submitForm: function(){
      document.forms['formHeader'].submit();
    }
  }
  // Metodo para adicionar horas
  Date.prototype.addHours = function(h) {
    h = h + 0;
    this.setTime(this.getTime() + (h*60*60*1000));
    //alert('time offset: ' + h + '\nTime: ' + this.getUTCHours() + ':' + this.getUTCMinutes());
    return this;
  }

  // Ponto de entrada da execução, ver { http://api.jquery.com/ready/ }
  $(document).ready(function() {
    cityInfoDiv = $('#city_info');
    cityInfoDiv.toggle(false);
    var infoSize = [cityInfoDiv.width(), cityInfoDiv.height()];

    // Define a repetição do trecho abaixo (1 segundo)
    setInterval( function() {
      // Inicializações
      Mateus.theDate = new Date();
      // Soma a diferença nos horários
      Mateus.theDate.addHours(queryOffset);
      Mateus.n_horas = Mateus.theDate.getUTCHours();
      Mateus.n_minutos = Mateus.theDate.getUTCMinutes();
      Mateus.n_segundos = Mateus.theDate.getUTCSeconds();
      
      // Execução
      Mateus.setCSSfor("#seg", Mateus.n_segundos * 6);
      Mateus.setCSSfor("#hor", Mateus.n_horas * 30 + (Mateus.n_minutos / 2));
      Mateus.setCSSfor("#min", Mateus.n_minutos * 6);
      }, 1000 
    );
    
    // Google Maps
    var options = {
      zoom: Mateus.mapZoomDefault,
      center: new google.maps.LatLng(51.47, 0), //Brasilia
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    Mateus.map = new google.maps.Map(document.getElementById("map_canvas"), options);
    Mateus.geocoder = new google.maps.Geocoder();
    Mateus.geocoder.geocode( 
      { 'address': queryAddress.substring(queryAddress.lastIndexOf('-') + 1).replace('_',' ')}, 
      function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          var bounds = new google.maps.LatLngBounds();
          for(var i = 0; i < results.length; i++){
            var marker = new google.maps.Marker({
                map: Mateus.map,
                title: 'GMT ' + queryOffset,
                position: results[i].geometry.location
            });
            bounds.extend(marker.position);
          }
          Mateus.map.setCenter(bounds.getCenter());
          Mateus.map.fitBounds(bounds);
        } else {
          if (status != google.maps.GeocoderStatus.ZERO_RESULTS)
            alert("Erro carregando localização.\n Resposta: " + status);
        }
        if (Mateus.map.getZoom() > Mateus.mapZoomDefault)
          Mateus.map.setZoom(Mateus.mapZoomDefault);
      }
    );

    // Binda um eventhandler para alterações no tamanho da janela (já que o painel de informações depende da largura da janela)
    $(window).resize(function(){
      cityInfoDiv.width(Math.min(infoSize[0] + 20, $('body').width() - 220));
    });

    // Busca os dados para popular o painel de informações sobre a cidade
    $.ajax({
      type: 'GET',
      url: 'semantic.php',
      cache: true,
      data: ({ term: queryAddress.substring(queryAddress.lastIndexOf('-') + 1).replace('_',' ')}),
      success: function(resp){
        // Preenche o div com a informação recebida
        cityInfoDiv.html(resp).find('a[href^="http://"]').attr({ target: "_blank" });

        // Calcula o tamanho do painel
        cityInfoDiv.width(Math.min(cityInfoDiv.width(), $('body').width() - 220));
        infoSize = [cityInfoDiv.width(), cityInfoDiv.height()];        

        // Faz a animação pra mostrar
        cityInfoDiv.width(0).height(0).toggle(true).animate({
          opacity: .92,
          width: Math.min(infoSize[0], $('body').width() - 220),
          height: Math.min(190, infoSize[1])
        });
      },
      error: function() {
        cityInfoDiv.css('font-weight', 'bold').toggle(true);
      }
    });
  });
