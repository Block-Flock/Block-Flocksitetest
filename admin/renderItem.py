
        
        import bpy
        bpy.ops.wm.open_mainfile(filepath="/var/www/html/resources/core/rendering/avatar/head.blend")
        HeadImg = bpy.data.images.load(filepath="/var/www/html/assets/images/store//tmp/phpgIJseI.png")
        HeadTex = bpy.data.textures.new("ColorTex", type = "IMAGE")
        HeadTex.image = HeadImg
        Headslot = bpy.data.objects["Head"].active_material.texture_slots.add()
        Headslot.texture = HeadTex
        
        
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
        bpy.data.scenes["Scene"].render.filepath = "/var/www/html/assets/images/store/d335ed7acd39e855589a9b45fc70720b.png"
        bpy.ops.render.render( write_still=True )
        
        