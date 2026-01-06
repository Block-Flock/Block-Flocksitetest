<?php
$title = 'Report Ads';
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/header.php');

?>
<section class="section">
<div class='container'>
        <div class='columns'>
             <div class='column is-3'>
             </div>
             <div class='column is-half-desktop is-four-fifths-tablet'>
                 <div class='box'>
                 <?php

        ?>
<div class="novello-title is-size-4" >Report this Ad</div>

<form action="" method='POST'>
   <div class='field'>
      <div class='control'>
         <div class='field'>
            <label class='label'>Reason <a href="/">(False Alarm)</a></label>
            <textarea type='text' name='' class='textarea' placeholder="The reason i'd like to report this ad..."></textarea>
            <p class="novello has-text-grey-light is-size-7">Please give a good reason on why you would like to report this user ad..</p>
         </div>
         <div class='field'>
            <input type='submit'  name='' class='button is-info is-outlined' value='Submit'/>
         </div>
      </div>
   </div>
</form>
</div>
</div>
</div>
</section>

<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php');
?>