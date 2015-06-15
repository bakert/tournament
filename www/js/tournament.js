var TOURNAMENT = {
  'init': function () {
    $('tr[data-href]').on("click", function() {
      document.location = $(this).data('href');
    });
  }
};

TOURNAMENT.init();