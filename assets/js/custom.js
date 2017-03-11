$(document).ready(function(){
  $(document).on('pjax:end', function (xhr, options) {
    switch(xhr.target.id)
    {
      case 'div-workers':
        $(xhr.target).find('.fa-refresh').removeClass('fa-spin');
        break;
    }
  });
  $(document).on('pjax:start', function(xhr,options){
    switch(xhr.target.id)
    {
      case 'div-workers':
        $(xhr.target).find('.fa-refresh').addClass('fa-spin');
        break;
    }
  });
  $(document).on('pjax:success', function(xhr, data, status, options) {
    switch(xhr.target.id)
    {
      case 'form-wall':
        break;
    }
  });
  $(document).on('pjax:error', function(xhr, textStatus, error, options) {
    switch(xhr.target.id)
    {
      case 'div-worker':
        $(xhr.target).find('.fa-refresh').removeClass('fa-spin');
        break;
    }
  });
});