jQuery(document).ready(function(){
  jQuery(".owl-carousel").owlCarousel();


  if(jQuery("#edit-cerca").length){
    new TypeWriter('#edit-cerca', [
      'Emilio Lussu',
      'della libertà di stampa',
      'Ottone Bacaredda',
      'Facoltà di Medicina e Chirurgia',
      'sulla febbre gastrica',
      'Domenico Lovisato',
      'Antonio Pacinotti',
      'Facoltà di Giurisprudenza',
      'Eva Mameli Calvino',
      'Giovanni Spano',
      'Facoltà di Teologia',
      'Paola Maria Arcari',
      'Giovanni Maria Dettori',
      'Facoltà di Scienze fisiche, matematiche e naturali',
      'Giovanni Maria Angioj',
      'Giuseppe Brotzu',
      'Facoltà di Lettere e Filosofia',
      'Antonio Segni',
      ],
      { writeDelay: 100 });
  }

  if(jQuery("#ricercahome").length){
    new TypeWriter('#ricercahome', [
      'Emilio Lussu',
      'della libertà di stampa',
      'Ottone Bacaredda',
      'Facoltà di Medicina e Chirurgia',
      'sulla febbre gastrica',
      'Domenico Lovisato',
      'Antonio Pacinotti',
      'Facoltà di Giurisprudenza',
      'Eva Mameli Calvino',
      'Giovanni Spano',
      'Facoltà di Teologia',
      'Paola Maria Arcari',
      'Giovanni Maria Dettori',
      'Facoltà di Scienze fisiche, matematiche e naturali',
      'Giovanni Maria Angioj',
      'Giuseppe Brotzu',
      'Facoltà di Lettere e Filosofia',
      'Antonio Segni',
      ],
      { writeDelay: 100 });
  }
});
