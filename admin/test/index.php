<?php $title = 'Admin Panel';

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');
         
         
if(!$AUTH) {

    http_response_code(404);
    die;

}

if($AUTH && $my->administrator == 0) {

    http_response_code(404);
    die;

}
         
         
         
    $playersacc = $db->prepare("SELECT `id` FROM `users` ORDER BY `id` DESC LIMIT 1");
    $playersacc->execute();
    $players = $playersacc->fetch(PDO::FETCH_OBJ);

    $banned = $db->prepare("SELECT `id` FROM `user_bans` ORDER BY `id` DESC LIMIT 1");
    $banned->execute();
    $bans = $banned->fetch(PDO::FETCH_OBJ);  
     

    $items = $db->prepare("SELECT `id` FROM `store_items` ORDER BY `id` DESC LIMIT 1");
    $items->execute();
    $store = $items->fetch(PDO::FETCH_OBJ);  

    $group = $db->prepare("SELECT `id` FROM `user_groups` ORDER BY `id` DESC LIMIT 1");
    $group->execute();
    $user = $group->fetch(PDO::FETCH_OBJ);  


if(isset($_POST['grantPremium'])){
  $wavescripts = htmlspecialchars($_POST['u']);
  $kOne = 1;
$addPrem = $db->prepare("UPDATE `users` SET `premium` = :oneVal WHERE `id` = :wave");
$addPrem->bindParam(":oneVal", $kOne, PDO::PARAM_INT);
$addPrem->bindParam(":wave", $wavescripts, PDO::PARAM_INT);
$addPrem->execute();
$loginsert = $db->prepare('INSERT INTO user_history (admin, user, action, timemoderated) VALUES (?, ?, ?, ?)');
$loginsert->bindValue(1, $_SESSION['u'], PDO::PARAM_INT);
$loginsert->bindValue(2, $wavescripts, PDO::PARAM_INT);
$loginsert->bindValue(3, 'Granted Premium', PDO::PARAM_STR);
$loginsert->bindValue(4, time(), PDO::PARAM_INT);
$loginsert->execute();
//your welcome for logs kinda sus of mods rn -Intel
$strGrant2 = "Granted premium membership to user ID ".$wavescripts."";


echo"Successfully granted premium.";
echo"<script>window.location.replace('/user/admi');</script>";
}

if(isset($_POST['grantVerified'])){
    $wavescripts = htmlspecialchars($_POST['u']);
    $kOne = 1;
  $addPrem = $db->prepare("UPDATE `users` SET `verified` = :oneVal WHERE `id` = :wave");
  $addPrem->bindParam(":oneVal", $kOne, PDO::PARAM_INT);
  $addPrem->bindParam(":wave", $wavescripts, PDO::PARAM_INT);
  $addPrem->execute();
  $loginsert = $db->prepare('INSERT INTO user_history (admin, user, action, timemoderated) VALUES (?, ?, ?, ?)');
  $loginsert->bindValue(1, $_SESSION['u'], PDO::PARAM_INT);
  $loginsert->bindValue(2, $wavescripts, PDO::PARAM_INT);
  $loginsert->bindValue(3, 'Granted Verified', PDO::PARAM_STR);
  $loginsert->bindValue(4, time(), PDO::PARAM_INT);
  $loginsert->execute();
  //your welcome for logs kinda sus of mods rn -Intel
  $strGrant2 = "Granted Verified membership to user ID ".$wavescripts."";
  
  echo"Successfully granted premium.";
  echo"<script>window.location.replace('/user/admi');</script>";
  }

if(isset($_POST['grantCurrency'])){
    $wavescripts = htmlspecialchars($_POST['id']);
    $coins = htmlspecialchars($_POST['coins']);
  $addCurrency = $db->prepare("UPDATE `users` SET `coins` = `coins` + :gimme WHERE `id` = :uIDD");
  $addCurrency->bindParam(":gimme", $coins, PDO::PARAM_INT);
  $addCurrency->bindParam(":uIDD", $wavescripts, PDO::PARAM_INT);
  $addCurrency->execute();
  $loginsert = $db->prepare('INSERT INTO user_history (admin, user, action, timemoderated) VALUES (?, ?, ?, ?)');
  $loginsert->bindValue(1, $_SESSION['u'], PDO::PARAM_INT);
  $loginsert->bindValue(2, $wavescripts, PDO::PARAM_INT);
  $loginsert->bindValue(3, 'Granted '.$coins.' Coins', PDO::PARAM_STR);
  $loginsert->bindValue(4, time(), PDO::PARAM_INT);
  $loginsert->execute();
  //your welcome for logs kinda sus of mods rn -Intel
  $strGrant = "Granted ".$coins." coins to user ID ".$wavescripts."";

  echo"Successfully granted coins.";
  echo"<script>window.location.replace('/user/admi');</script>";
}
?>

        <div class="container-fluid">
        <link href='/assets/wave.css' rel='stylesheet'>
<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    <a href="/user/dashboard" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
            class="fas fa-home fa-sm text-white-50"></i> Back</a>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Store Items</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $store->id;?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-shopping-basket fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $players->id;?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tasks
                        </div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">45%</div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: 45%" aria-valuenow="45" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Pending Reports</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">0</div>
                    </div>
                    <div class="col-auto">
                        <i class="fal fa-comments fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">

    <!-- Content Column -->
    <div class="col-lg-6 mb-4">

        <!-- Project Card Example -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Projects</h6>
            </div>
            <div class="card-body">
                <h4 class="small font-weight-bold">Bans <span
                        class="float-right"><?php echo $bans->id;?>%</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $bans->id;?>%"
                        aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <h4 class="small font-weight-bold">Groups <span
                        class="float-right"><?php echo $user->id;?>%</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $user->id;?>%"
                        aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <h4 class="small font-weight-bold">Reports<span
                        class="float-right">0%</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar" role="progressbar" style="width: 0%"
                        aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <h4 class="small font-weight-bold">Payments <span
                        class="float-right">2%</span></h4>
                <div class="progress mb-4">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 2%"
                        aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        <!-- Color System -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body">
                       Coins
                        <br>
                        <br>
                          <form action="" method="POST">
      <div style="height: 10px;"></div>
      <input class="form-control" type="text" name="id" placeholder="User ID">
      <div style="height: 10px;"></div>
      <input class="form-control" type="text" name="coins" placeholder="Amount of Coins">
      <div style="height: 10px;"></div>
      <input class="btn btn-success" name="grantCurrency" type="submit" value="Grant Currency">
      </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        Premium Badge
                      <br>
                    <br>
                    <form action="" method="POST"> 
      <div style="height: 10px;"></div>
      <input class="form-control" type="text" name="u" placeholder="User ID">
      <div style="height: 10px;"></div>
      <input class="btn btn-success" name="grantPremium" type="submit" value="Grant">
    </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card bg-info text-white shadow">
                    <div class="card-body">
                        Verified Badge
                        <Br><br><form action="" method="POST"> 
      <div style="height: 10px;"></div>
      <input class="form-control" type="text" name="u" placeholder="User ID">
      <div style="height: 10px;"></div>
      <input class="btn btn-success" name="grantVerified" type="submit" value="Grant">
    </form>
           </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card bg-danger text-white shadow">
                    <div class="card-body">
                        Site offline Switch
                        <br>
                        <button type="button" class="btn btn-danger btn-lg btn-block" href="#" data-toggle="modal" data-target="#offlineModal">
                                    <i class="fas fa-hammer fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Continue
                                </button>
                        <div class="modal fade" id="offlineModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to put the site offline?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Submit Request" to put this session offline.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="#">Submit Request</a>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-6 mb-4">

        <!-- Ban Notes -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Ban Notes</h6>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 25rem;"
                        src="/assets/achievements/admi.png" alt="">
                </div>
                <hr>
<li>Term- You are no longer welcome on Novello.
<br>
<li>Name Hoarding- Please do not create accounts for the purpose of making names not being able to be used.
<br>
<li>Ban Request- Your account has been deleted as per your request. Thank you for being a part of the Novello community.
<br>
<li>Spam- Do not post spam or unnecessary content on the forums.
<br>
<li>False info- Do not spread false information with the intention of causing fear.
<br>
<li>Coinfarming- Do not use alternate accounts to generate currency.
<br>
<li>Limited alt hoarding- Do not use alternate accounts to purchase multiple special items.
<br>
<li>Derogatory language- Do not use derogatory or discriminatory language on Novello.
<br>
<li>Inappropriate language- Do not excessively use inappropriate or sexual language on Novello.
<br>
<li>Inappropriate discussion- Do not discuss topics that are strictly inappropriate or sexual on Novello. 
<br>
<li>Harassing users- Do not harass other users on Novello.
<br>
<li>Impersonation- Do not impersonate other users, especially staff, on Novello.
<br>
<li>Stealing clothes- Do not steal clothing from other users on Novello.
<br>
<li>Scamming- Do not take advantage of or scam other users on Novello.</p>
                <strong> &rarr; From WaveScripts</strong>
            </div>
        </div>

        <!-- Approach -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">To Do</h6>
            </div>
            <div class="card-body">
                <p>you guys</p>
                <br>
                <p>I can't emphasize enough that i got a new keyboard & bought 3d all stars for the switch</p>
                <br>
                <p>Community Rules</p>
                <br>
                <p class="mb-0">Could be done by any developer viewing this and has access to the files.</p>
                
            </div>
        </div>
    </div>
</div>
</div>
<!-- /.container-fluid -->

</div>


  <!-- Custom scripts for all pages-->
  <script src="/vendor/jquery/jquery.min.js"></script>
    <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="/vendor/jquery-easing/jquery.easing.min.js"></script>

    <script src="/assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="/vendor/chart.js/Chart.min.js"></script>

<!-- Page level custom scripts -->
<script src="/assets/js/demo/chart-area-demo.js"></script>
<script src="/assets/js/demo/chart-pie-demo.js"></script>

<?php include $root.'/includes/footer.php'; ?>