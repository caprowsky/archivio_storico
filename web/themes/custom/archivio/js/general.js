jQuery(document).ready(function(){
  jQuery(".owl-carousel").owlCarousel();


  if(jQuery("#edit-cerca").length){
    new TypeWriter('#edit-cerca', [
        'Giuseppe Puddu Sassari',
        'Argiolas ingegneria',
      ],
      { writeDelay: 100 });
  }

  if(jQuery("#ricercahome").length){
    new TypeWriter('#ricercahome', [
      'Giuseppe Puddu Sassari',
      'Argiolas ingegneria',
      ],
      { writeDelay: 100 });
  }
});
