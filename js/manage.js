$(function(){
  $('.checkboxes-helpers').each(function(){dotclear.checkboxesHelpers(this, undefined, '#form-entries td input[type=checkbox]', '#form-entries #do-action');});
  dotclear.condSubmit('#form-entries td input[type=checkbox]', '#form-entries #do-action');
});