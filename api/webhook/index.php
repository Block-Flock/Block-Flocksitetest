<?php
$title = 'Test Systems - Webhook';
require_once($_SERVER['DOCUMENT_ROOT'].'/private/header.inc.php');

if(!$AUTH) {

    http_response_code(404);
    die;

}

if($AUTH && $my->administrator == 0) {

    http_response_code(404);
    die;

}

if(isset($_POST['sendwebhookthing']) && $AUTH && $my->administrator >= 1) {
	$webhookurl = "https://discord.com/api/webhooks/991091091946213426/yZTPCnbtJmw8KZm06GhE-dm_iQbEZK6R0UfR-25CKXmyP1YvaY5CyGUTydjWsCjnevr-";

if(isset($_POST['content'])){
	$content = $_POST['content'];
}else{
	$content = '';
}

if(isset($_POST['title'])){
	$title = $_POST['title'];
}else{
	$title = '';
}

if(isset($_POST['description'])){
	$description = $_POST['description'];
}else{
	$description = '';
}

if(isset($_POST['url'])){
	$url = $_POST['url'];
}else{
	$url = "https://".$_SERVER[HTTP_HOST]."/store/view/";
}

if(isset($_POST['imageurl'])){
	$imageurl = $_POST['imageurl'];
}else{
	$imageurl = '';
}

if(isset($_POST['creatorname'])){
	$creatorname = $_POST['creatorname'];
}else{
	$creatorname = 'Bricktaria Foundation';
}

if(isset($_POST['creatorid'])){
	$creatorid = $_POST['creatorid'];
}else{
	$creatorid = '1';
}

if(isset($_POST['price'])){
	$price = $_POST['price'];
}else{
	$price = '0';
}

if(isset($_POST['stock'])){
	$stock = $_POST['stock'];
}else{
	$stock = 'Unlimited';
}

if(isset($_POST['special'])){
	$special = $_POST['special'];
}else{
	$special = 'False';
}

$timestamp = date("c", strtotime("now"));

$json_data = json_encode([
    // Message
    "content" => $content,

    // Username
    "username" => "Item Notifier",

    // Avatar URL.
    // Uncoment to replace image set in webhook
    //"avatar_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=512",

    // Text-to-speech
    "tts" => false,

    // File upload
    // "file" => "",

    // Embeds Array
    "embeds" => [
        [
            // Embed Title
            "title" => $title,

            // Embed Type
            "type" => "rich",

            // Embed Description
            "description" => $description,

            // URL of title link
            "url" => $url,

            // Timestamp of embed must be formatted as ISO8601
            "timestamp" => $timestamp,

            // Embed left border color in HEX
            "color" => hexdec( "3366ff" ),

            // Footer
            "footer" => [
                "text" => "Copyright � 202Bricktariaia, Inc. All rights reserved."
            ],

            // Image to send
            "image" => [
                "url" => $imageurl
            ],

            // Thumbnail
            //"thumbnail" => [
            //    "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=400"
            //],

            // Author
            "author" => [
                "name" => $creatorname,
                "url" => "https://".$_SERVER[HTTP_HOST]."/user/profile/?id=".$creatorid
            ],

            // Additional Fields array
            "fields" => [
                // Field 1
                [
                    "name" => "Price",
                    "value" => $price,
                    "inline" => false
                ],
                [
                    "name" => 'Special',
                    "value" => $special,
                    "inline" => false
                ],
                [
                    "name" => 'Stock',
                    "value" => $stock,
                    "inline" => true
                ]
            ]
        ]
    ]

], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


$ch = curl_init( $webhookurl );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt( $ch, CURLOPT_POST, 1);
curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt( $ch, CURLOPT_HEADER, 0);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec( $ch );
// If you need to debug, or find out why you can't send message uncomment line below, and execute script.
// echo $response;
curl_close( $ch );
}

?>

<form method="post" enctype="multipart/form-data">
<div class="field">
<input class="input" type="text" name="title" placeholder="Title"></input>
</div>
<div class="field">
<textarea class="textarea" type="textarea" name="content" placeholder="Content"></textarea>
</div>
<div class="field">
<textarea class="textarea" type="textarea" name="description" placeholder="Description"></textarea>
</div>
<field>
<button type="submit" name="sendwebhookthing" class="button is-success is-fullwidth">Send Webhook</button>
</field>
</form>

<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'); ?>