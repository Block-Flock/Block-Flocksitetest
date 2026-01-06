<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

if($AUTH) {
    $uid = $_SESSION['id'];

    if(isset($_GET['ldr'])) {
        $uid = $_GET['ldr'];
    }

    $AvatarHash = bin2hex(random_bytes(16));
  
  if($my->flood >= time()) {

        die('Please wait another '.($my->flood-time()).' seconds before changing your colors.');

    }

    $getColors = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
    $getColors->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $getColors->execute();

    $gc = $getColors->fetch(PDO::FETCH_OBJ);

    if($gc->head == 'FFFFFF') {

        $headclr = '1,1,1';

    } else {

        $headclr = $gc->head;

    }

    if($gc->torso == '3398db') {

        $torsoclr = '51, 152, 219';

    } else {

        $torsoclr = $gc->torso;

    }

    if($gc->left_arm == 'FFFFFF') {

        $left_armclr = '1,1,1';

    } else {

        $left_armclr = $gc->left_arm;

    }

    if($gc->right_arm == 'FFFFFF') {

        $right_armclr = '1,1,1';

    } else {

        $right_armclr = $gc->right_arm;

    }

    if($gc->left_leg == 'FFFFFF') {

        $left_legclr = '1,1,1';

    } else {

        $left_legclr = $gc->left_leg;

    }

    if($gc->right_leg == 'FFFFFF') {

        $right_legclr = '1,1,1';

    } else {

        $right_legclr = $gc->right_leg;

    }


    $getWear = $db->prepare('SELECT * FROM user_data WHERE user = ?');
    $getWear->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
    $getWear->execute();

    $gw = $getWear->fetch(PDO::FETCH_OBJ);

 if(!empty($gw->gear)) {
  $code = '
import bpy
bpy.ops.wm.open_mainfile(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/avatar/avatar10.blend")';}else{
  $code = '
import bpy
bpy.ops.wm.open_mainfile(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/avatar/avatar.blend")';}
 if(!empty($gw->hat1)) { $code1 = '
hatPath = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat1.'/'.$gw->hat1.'.obj"
importHat = bpy.ops.import_scene.obj(filepath=hatPath)
hat = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
hatImage = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat1.'/'.$gw->hat1.'.png")
hatTexture = bpy.data.textures.new("ColorTex", type = "IMAGE")
hatTexture.image = hatImage
hatMaterial = bpy.data.materials.new("MaterialName")
hatMaterial.diffuse_shader = "LAMBERT"
hatSlot = hatMaterial.texture_slots.add()
hatSlot.texture = hatTexture
hat.active_material = hatMaterial'; } else { $code1 = ''; }
 if(!empty($gw->hat2)) { $code2 = '

hatPath2 = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat2.'/'.$gw->hat2.'.obj"
importHat2 = bpy.ops.import_scene.obj(filepath=hatPath2)
hat2 = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
hatImage2 = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat2.'/'.$gw->hat2.'.png")
hatTexture2 = bpy.data.textures.new("ColorTex", type = "IMAGE")
hatTexture2.image = hatImage2
hatMaterial2 = bpy.data.materials.new("MaterialName")
hatMaterial2.diffuse_shader = "LAMBERT"
hatSlot2 = hatMaterial2.texture_slots.add()
hatSlot2.texture = hatTexture2
hat2.active_material = hatMaterial2'; } else { $code2 = ''; }

if(!empty($gw->hat3)) { $code3 = '

hatPath3 = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat3.'/'.$gw->hat3.'.obj"
importHat3 = bpy.ops.import_scene.obj(filepath=hatPath3)
hat3 = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
hatImage3 = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat3.'/'.$gw->hat3.'.png")
hatTexture3 = bpy.data.textures.new("ColorTex", type = "IMAGE")
hatTexture3.image = hatImage3
hatMaterial3 = bpy.data.materials.new("MaterialName")
hatMaterial3.diffuse_shader = "LAMBERT"
hatSlot3 = hatMaterial3.texture_slots.add()
hatSlot3.texture = hatTexture3
hat3.active_material = hatMaterial3'; } else { $code3 = ''; } 

if(!empty($gw->gear)) { $code4 = '
gearpath = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/gear/'.$gw->gear.'/'.$gw->gear.'.obj"
import_gear = bpy.ops.import_scene.obj(filepath=gearpath)
gear = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
gearImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/gear/'.$gw->gear.'/'.$gw->gear.'.png")
gearTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
gearTex.image = gearImg
gearMat = bpy.data.materials.new("MaterialName")
gearMat.diffuse_shader = "LAMBERT"
gearSlot = gearMat.texture_slots.add()
gearSlot.texture = gearTex
gear.active_material = gearMat'; }else { $code4 = ''; }

if(!empty($gw->accessory)) { $code5 = '
    accessorypath = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/accessories/'.$gw->accessory.'/'.$gw->accessory.'.obj"
    import_accessory = bpy.ops.import_scene.obj(filepath=accessorypath)
    accessory = bpy.context.selected_objects[0]
    bpy.context.selected_objects[0].data.name = "default"
    accessoryImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/accessories/'.$gw->accessory.'/'.$gw->accessory.'.png")
    accessoryTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
    accessoryTex.image = accessoryImg
    accessoryMat = bpy.data.materials.new("MaterialName")
    accessoryMat.diffuse_shader = "LAMBERT"
    accessorySlot = accessoryMat.texture_slots.add()
    accessorySlot.texture = accessoryTex
    accessory.active_material = accessoryMat'; }else { $code5 = ''; }


if(!empty($gw->head)) { $code6 = '
Headpath = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/head/'.$gw->head.'/'.$gw->head.'.obj"
import_Head = bpy.ops.import_scene.obj(filepath=Headpath)
Head = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
HeadImage = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/faces/'.$gw->face.'.png")
HeadTexture = bpy.data.textures.new("ColorTex", type = "IMAGE")
HeadTexture.image = HeadImage
HeadAdd = bpy.data.objects["Head"].active_material.texture_slots.add()
HeadAdd.texture = HeadTexture'; }else { $code6 = ''; }
$code7 = '


HeadImage = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/faces/'.$gw->face.'.png")
HeadTexture = bpy.data.textures.new("ColorTex", type = "IMAGE")
HeadTexture.image = HeadImage
HeadAdd = bpy.data.objects["Head"].active_material.texture_slots.add()
HeadAdd.texture = HeadTexture

LeftArmImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->shirt.'.png")
LeftArmTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
LeftArmTex.image = LeftArmImg
LeftArmslot = bpy.data.objects["LArm"].active_material.texture_slots.add()
LeftArmslot.texture = LeftArmTex

RightArmImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->shirt.'.png")
RightArmTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
RightArmTex.image = RightArmImg
RightArmslot = bpy.data.objects["RArm"].active_material.texture_slots.add()
RightArmslot.texture = RightArmTex

TorsoImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->shirt.'.png")
TorsoTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
TorsoTex.image = TorsoImg
Torsolot = bpy.data.objects["Torso"].active_material.texture_slots.add()
Torsolot.texture = TorsoTex

TorsoImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->tshirt.'.png")
TorsoTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
TorsoTex.image = TorsoImg
Torsolot = bpy.data.objects["Torso"].active_material.texture_slots.add()
Torsolot.texture = TorsoTex

LeftLegImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->pants.'.png")
LeftLegTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
LeftLegTex.image = LeftLegImg
LeftLegslot = bpy.data.objects["LLeg"].active_material.texture_slots.add()
LeftLegslot.texture = LeftLegTex

RightLegImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->pants.'.png")
RightLegTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
RightLegTex.image = RightLegImg
RightLegslot = bpy.data.objects["RLeg"].active_material.texture_slots.add()
RightLegslot.texture = RightLegTex

bpy.data.objects["Head"].select = True
bpy.data.objects["Head"].active_material.diffuse_color = ('.$headclr.')

bpy.data.objects["Torso"].select = True
bpy.data.objects["Torso"].active_material.diffuse_color = ('.$torsoclr.')

bpy.data.objects["RArm"].select = True
bpy.data.objects["RArm"].active_material.diffuse_color = ('.$right_armclr.')

bpy.data.objects["LArm"].select = True
bpy.data.objects["LArm"].active_material.diffuse_color = ('.$left_armclr.')

bpy.data.objects["LLeg"].select = True
bpy.data.objects["LLeg"].active_material.diffuse_color = ('.$left_legclr.')


bpy.data.objects["RLeg"].select = True
bpy.data.objects["RLeg"].active_material.diffuse_color = ('.$right_legclr.')

for ob in bpy.context.scene.objects:
    if ob.type == "MESH":
        ob.select = True
        bpy.context.scene.objects.active = ob
    else:
        ob.select = False
bpy.ops.object.join()

bpy.ops.view3d.camera_to_view_selected()
origAlphaMode = bpy.data.scenes["Scene"].render.alpha_mode
bpy.data.scenes["Scene"].render.alpha_mode = "TRANSPARENT"
bpy.data.scenes["Scene"].render.alpha_mode = origAlphaMode
bpy.data.scenes["Scene"].render.filepath = "'.$_SERVER['DOCUMENT_ROOT'].'/assets/images/avatars/'.$AvatarHash.'.png"
bpy.ops.render.render( write_still=True )
';

$code8 = '
import bpy
bpy.ops.wm.open_mainfile(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/avatar/avatar-headshot.blend")';
 if(!empty($gw->hat1)) { $code9 = '
hatPath = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat1.'/'.$gw->hat1.'.obj"
importHat = bpy.ops.import_scene.obj(filepath=hatPath)
hat = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
hatImage = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat1.'/'.$gw->hat1.'.png")
hatTexture = bpy.data.textures.new("ColorTex", type = "IMAGE")
hatTexture.image = hatImage
hatMaterial = bpy.data.materials.new("MaterialName")
hatMaterial.diffuse_shader = "LAMBERT"
hatSlot = hatMaterial.texture_slots.add()
hatSlot.texture = hatTexture
hat.active_material = hatMaterial'; } else { $code9 = ''; }
 if(!empty($gw->hat2)) { $code10 = '

hatPath2 = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat2.'/'.$gw->hat2.'.obj"
importHat2 = bpy.ops.import_scene.obj(filepath=hatPath2)
hat2 = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
hatImage2 = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat2.'/'.$gw->hat2.'.png")
hatTexture2 = bpy.data.textures.new("ColorTex", type = "IMAGE")
hatTexture2.image = hatImage2
hatMaterial2 = bpy.data.materials.new("MaterialName")
hatMaterial2.diffuse_shader = "LAMBERT"
hatSlot2 = hatMaterial2.texture_slots.add()
hatSlot2.texture = hatTexture2
hat2.active_material = hatMaterial2'; } else { $code10 = ''; }

if(!empty($gw->hat3)) { $code11 = '

hatPath3 = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat3.'/'.$gw->hat3.'.obj"
importHat3 = bpy.ops.import_scene.obj(filepath=hatPath3)
hat3 = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
hatImage3 = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/hat/'.$gw->hat3.'/'.$gw->hat3.'.png")
hatTexture3 = bpy.data.textures.new("ColorTex", type = "IMAGE")
hatTexture3.image = hatImage3
hatMaterial3 = bpy.data.materials.new("MaterialName")
hatMaterial3.diffuse_shader = "LAMBERT"
hatSlot3 = hatMaterial3.texture_slots.add()
hatSlot3.texture = hatTexture3
hat3.active_material = hatMaterial3'; } else { $code11 = ''; }

if(!empty($gw->accessory)) { $code12 = '
accessorypath = "'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/accessories/'.$gw->accessory.'/'.$gw->accessory.'.obj"
import_accessory = bpy.ops.import_scene.obj(filepath=accessorypath)
accessory = bpy.context.selected_objects[0]
bpy.context.selected_objects[0].data.name = "default"
accessoryImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/accessories/'.$gw->accessory.'/'.$gw->accessory.'.png")
accessoryTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
accessoryTex.image = accessoryImg
accessoryMat = bpy.data.materials.new("MaterialName")
accessoryMat.diffuse_shader = "LAMBERT"
accessorySlot = accessoryMat.texture_slots.add()
accessorySlot.texture = accessoryTex
accessory.active_material = accessoryMat'; }else { $code12 = ''; }
 $code13 = '

HeadImage = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/faces/'.$gw->face.'.png")
HeadTexture = bpy.data.textures.new("ColorTex", type = "IMAGE")
HeadTexture.image = HeadImage
HeadAdd = bpy.data.objects["Head"].active_material.texture_slots.add()
HeadAdd.texture = HeadTexture

LeftArmImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->shirt.'.png")
LeftArmTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
LeftArmTex.image = LeftArmImg
LeftArmslot = bpy.data.objects["LArm"].active_material.texture_slots.add()
LeftArmslot.texture = LeftArmTex

RightArmImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->shirt.'.png")
RightArmTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
RightArmTex.image = RightArmImg
RightArmslot = bpy.data.objects["RArm"].active_material.texture_slots.add()
RightArmslot.texture = RightArmTex

TorsoImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->shirt.'.png")
TorsoTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
TorsoTex.image = TorsoImg
Torsolot = bpy.data.objects["Torso"].active_material.texture_slots.add()
Torsolot.texture = TorsoTex

TorsoImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->tshirt.'.png")
TorsoTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
TorsoTex.image = TorsoImg
Torsolot = bpy.data.objects["Torso"].active_material.texture_slots.add()
Torsolot.texture = TorsoTex

LeftLegImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->pants.'.png")
LeftLegTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
LeftLegTex.image = LeftLegImg
LeftLegslot = bpy.data.objects["LLeg"].active_material.texture_slots.add()
LeftLegslot.texture = LeftLegTex

RightLegImg = bpy.data.images.load(filepath="'.$_SERVER['DOCUMENT_ROOT'].'/admin/images/store/'.$gw->pants.'.png")
RightLegTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
RightLegTex.image = RightLegImg
RightLegslot = bpy.data.objects["RLeg"].active_material.texture_slots.add()
RightLegslot.texture = RightLegTex

bpy.data.objects["Head"].select = True
bpy.data.objects["Head"].active_material.diffuse_color = ('.$headclr.')

bpy.data.objects["Torso"].select = True
bpy.data.objects["Torso"].active_material.diffuse_color = ('.$torsoclr.')

bpy.data.objects["RArm"].select = True
bpy.data.objects["RArm"].active_material.diffuse_color = ('.$right_armclr.')

bpy.data.objects["LArm"].select = True
bpy.data.objects["LArm"].active_material.diffuse_color = ('.$left_armclr.')

bpy.data.objects["LLeg"].select = True
bpy.data.objects["LLeg"].active_material.diffuse_color = ('.$left_legclr.')


bpy.data.objects["RLeg"].select = True
bpy.data.objects["RLeg"].active_material.diffuse_color = ('.$right_legclr.')

origAlphaMode = bpy.data.scenes["Scene"].render.alpha_mode
bpy.data.scenes["Scene"].render.alpha_mode = "TRANSPARENT"
bpy.data.scenes["Scene"].render.alpha_mode = origAlphaMode
bpy.data.scenes["Scene"].render.filepath = "'.$_SERVER['DOCUMENT_ROOT'].'/assets/images/avatars/headshots/'.$AvatarHash.'.png"
bpy.ops.render.render( write_still=True )
';

$code_normal = "$code
$code1
$code2
$code3
$code4
$code5
$code6
$code7";

$headshotCode = "$code8
$code9
$code10
$code11
$code12
$code13";



    $handler = fopen($AvatarHash.'.py', 'w');
    $handler_headshot = fopen($AvatarHash.'-headshot.py', 'w');

    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/api/rendering/avatars/'.htmlspecialchars($AvatarHash).'.py', $code_normal);
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/api/rendering/avatars/'.htmlspecialchars($AvatarHash).'-headshot.py', $headshotCode);

    $updateImage = $db->prepare('UPDATE users SET avatar_hash = ? WHERE id = ?');
    $updateImage->bindValue(1, $AvatarHash, PDO::PARAM_STR);
    $updateImage->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
    $updateImage->execute();
  
        $flood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
        $flood->bindValue(1, time()+5, PDO::PARAM_STR);
        $flood->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $flood->execute();

    exec(escapeshellcmd('blender -b '.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/avatar/avatar.blend -P '.$_SERVER['DOCUMENT_ROOT'].'/api/rendering/avatars/'.htmlspecialchars($AvatarHash).'.py'));
    exec(escapeshellcmd('blender -b '.$_SERVER['DOCUMENT_ROOT'].'/JGFJFSHFSAH/core/rendering/avatar/avatar-headshot.blend -P '.$_SERVER['DOCUMENT_ROOT'].'/api/rendering/avatars/'.htmlspecialchars($AvatarHash).'-headshot.py'));

    if($my->administrator >= 1 || $my->ip == "111.11.111.111") {
        
        print_r($output);
        
    }

}