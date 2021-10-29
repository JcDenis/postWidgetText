$(function(){
  $('.checkboxes-helpers').each(function(){dotclear.checkboxesHelpers(this);});
  dotclear.condSubmit('#form-entries td input[type=checkbox]', '#form-entries #do-action');
});