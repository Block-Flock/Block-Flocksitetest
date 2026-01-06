

import bpy
bpy.ops.wm.open_mainfile(filepath="/var/www/html/resources/core/rendering/avatar/avatar.blend");



TorsoImg = bpy.data.images.load(filepath="/var/www/html/admin/images/store/8.png")
TorsoTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
TorsoTex.image = TorsoImg
Torsolot = bpy.data.objects["Torso"].active_material.texture_slots.add()
Torsolot.texture = TorsoTex
    

HeadImage = bpy.data.images.load(filepath="/var/www/html/resources/core/rendering/faces/0.png")
HeadTexture = bpy.data.textures.new("ColorTex", type = "IMAGE")
HeadTexture.image = HeadImage
HeadAdd = bpy.data.objects["Head"].active_material.texture_slots.add()
HeadAdd.texture = HeadTexture

bpy.data.objects["Head"].select = True
bpy.data.objects["Head"].active_material.diffuse_color = (0.723055, 0.791298, 0.806952)

bpy.data.objects["Torso"].select = True
bpy.data.objects["Torso"].active_material.diffuse_color = (0.723055, 0.791298, 0.806952)

bpy.data.objects["RArm"].select = True
bpy.data.objects["RArm"].active_material.diffuse_color = (0.723055, 0.791298, 0.806952)

bpy.data.objects["LArm"].select = True
bpy.data.objects["LArm"].active_material.diffuse_color = (0.723055, 0.791298, 0.806952)

bpy.data.objects["LLeg"].select = True
bpy.data.objects["LLeg"].active_material.diffuse_color = (0.723055, 0.791298, 0.806952)

bpy.data.objects["RLeg"].select = True
bpy.data.objects["RLeg"].active_material.diffuse_color = (0.723055, 0.791298, 0.806952)
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
bpy.data.scenes["Scene"].render.filepath = "/var/www/html/assets/images/store/9d8b930d7153f3f3d51bb6646cdf4efb.png"
bpy.ops.render.render( write_still=True )
    