<?php

	$start_time = time();

    

    while (1) {

        

        $current_time = time();

        if($current_time - $starbricktaria.com){

            exit();

        }

        

		$api = null;

		

    	$page = curl_init("https://bricktaria.com/store/");

    	curl_setopt($page, CURLOPT_RETURNTRANSFER, true);

    	curl_setopt($page, CURLOPT_FOLLOWLOCATION, true);

    	curl_setopt($page, CURLOPT_CONNECTTIMEOUT, 3);

    	curl_setopt($page, CURLOPT_TIMEOUT, 3);

    	$api = json_decode(curl_exec($page));

		curl_close($page);

		

    	if ($api == null) {

    	    exit();

    	}

        

        $id = $si->id;

        

        $query = $database->query('SELECT * FROM store_items LIMIT 1');

    

		$query = $query->fetch_assoc();

		$old_id = $query['id'];

        

        if ($id > 0 && $old_id > 0) {

            

            $escape_id = mysqli_real_escape_string($database, $id);

			

            if ($id != $old_id) {

                $embeds = array([

                    'type' => 'rich',

                    'title' => $si->name,

                    'description' => "\"" . $si->about . "\"\n\nView on [Epictest](/store/view/?id=)",

                    'fields' => array([

                        'name' => 'Price',

                        'inline' => true,

                        'value' => ($si->price == 1) ? number_format($si->price) . ' ' . $si->price : 'Offsale'

					], [

                        'name' => 'Special?',

                        'inline' => true,

                        'value' => ($si->tradeable== 1) ? '**Yes** (stock: ' . number_format($api->stock) . ')' : 'No'

					]),

                    'thumbnail' => ([

                        'url' => "/assets/images/store"

					]),

					'footer' => ([

                        'text' => 'Copyright � 2022 Bricktaria, Inc. All rights reserved.'

					]),

					'color' => ($si->tradeable == 1) ? 0xF5C800 : 0x3d3075

                ]);

                

                $data = array(

                    'content' => 'New item',

                    'embeds' => $embeds

                );

                

				$curl = curl_init('https://discord.com/api/webhooks/991392279841476698/cg9-41qAMoE_tELzcGOJXwDgkwbU_ymTvZcxonLn4fPfqiiVGRHdyteOLn1hRJEzWYx-');

                curl_setopt($curl, CURLOPT_CUSTOMREQUEST,'POST');

                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                echo curl_exec($curl);

				curl_close($curl);

				

                $database->query("UPDATE getItems(1, 1) SET id = '$escape_id'");

                

            }

		}

		

		sleep(5);

    }

?>










