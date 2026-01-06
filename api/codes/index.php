<?php
$title = "Generator";
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');
?>
<section class="section">
<div class="container">
        <div class="columns">
            <div class="column is-4">
            </div>
            <div class="column is-5">
            <div class="box" id="loginBox">
                        <div class='field'>
   <label class='label'>Hey, needing a password strong password?</label>
                                <input type='text' class='input' id="apikey" placeholder="Click Generate to generate a password" disabled>
                        </div>
                        <div class='field'>
 <input type='submit' id="keygen" class='button is-primary' value='Generate'/>
</section>



<script>

/**
 * Function to produce UUID.
 * See: http://stackoverflow.com/a/8809472
 */
function generateUUID()
{
  var d = new Date().getTime();
  
  if( window.performance && typeof window.performance.now === "function" )
  {
    d += performance.now();
  }
  
  var uuid = 'xx4xyxxxxxx'.replace(/[xy]/g, function(c)
  {
    var r = (d + Math.random()*15)%15 | 0;
    d = Math.floor(d/15);
    return (c=='x' ? r : (r&0x3|0x8)).toString(16);
  });

return uuid;
}

/**
 * Generate new key and insert into input value
 */
$( '#keygen' ).on('click',function()
{
  $( '#apikey' ).val( generateUUID() );
});

</script>






















