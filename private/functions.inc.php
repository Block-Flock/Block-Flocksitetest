<?php
class context {
  
public static function humanTiming ($time) {
    $time = time() - $time;
    $time = ($time < 1) ? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
    }
    return 'just now';
}
  
  public static function sendDiscordAdminLogs($message) {
    $url = "https://discord.com/api/webhooks/991091091946213426/yZTPCnbtJmw8KZm06GhE-dm_iQbEZK6R0UfR-25CKXmyP1YvaY5CyGUTydjWsCjnevr-";
    $dataArray = array('content' => $message,
    'username' => "Admin Logger");
    
    $httpOptions = array(
        'http' => array (
            'header' => "Bricktaria-Server",
            'content-type' => 'multipart/form-data',
            'method' => "POST",
            'content' => http_build_query($dataArray)
        )
    );
    
    $context = stream_context_create($httpOptions);
    $result = @file_get_contents($url, false, $context);
  }
}

// Global Helper Functions

function getAvatarData($userId, $userColors, $userWearables) {
    $colors = [
        'head' => [1, 0.8, 0.6],
        'torso' => [0.2, 0.2, 0.8],
        'leftArm' => [1, 0.8, 0.6],
        'rightArm' => [1, 0.8, 0.6],
        'leftLeg' => [0.1, 0.1, 0.1],
        'rightLeg' => [0.1, 0.1, 0.1]
    ];
    
    $items = [
        'hats' => [],
        'face' => 'default',
        'accessory' => 0,
        'tshirt' => 0,
        'shirt' => 0,
        'pants' => 0,
        'gear' => 0
    ];
    
    // Load colors
    if (isset($userColors[$userId])) {
        $colorData = $userColors[$userId];
        if (!empty($colorData->head)) {
            $headParts = explode(',', $colorData->head);
            if (count($headParts) >= 3) {
                $colors['head'] = [
                    max(0, min(1, floatval($headParts[0]))),
                    max(0, min(1, floatval($headParts[1]))),
                    max(0, min(1, floatval($headParts[2])))
                ];
            }
        }
        if (!empty($colorData->torso)) {
            $torsoParts = explode(',', $colorData->torso);
            if (count($torsoParts) >= 3) {
                $colors['torso'] = [
                    max(0, min(1, floatval($torsoParts[0]))),
                    max(0, min(1, floatval($torsoParts[1]))),
                    max(0, min(1, floatval($torsoParts[2])))
                ];
            }
        }
        if (!empty($colorData->left_arm)) {
            $armParts = explode(',', $colorData->left_arm);
            if (count($armParts) >= 3) {
                $colors['leftArm'] = [
                    max(0, min(1, floatval($armParts[0]))),
                    max(0, min(1, floatval($armParts[1]))),
                    max(0, min(1, floatval($armParts[2])))
                ];
                $colors['rightArm'] = $colors['leftArm'];
            }
        }
        if (!empty($colorData->left_leg)) {
            $legParts = explode(',', $colorData->left_leg);
            if (count($legParts) >= 3) {
                $colors['leftLeg'] = [
                    max(0, min(1, floatval($legParts[0]))),
                    max(0, min(1, floatval($legParts[1]))),
                    max(0, min(1, floatval($legParts[2])))
                ];
                $colors['rightLeg'] = $colors['leftLeg'];
            }
        }
    }
    
    // Load wearables including face
    if (isset($userWearables[$userId])) {
        $wearableData = $userWearables[$userId];
        
        // Handle hats (legacy format: hat1, hat2, hat3)
        $hats = [];
        if (!empty($wearableData->hat1) && $wearableData->hat1 > 0) $hats[] = intval($wearableData->hat1);
        if (!empty($wearableData->hat2) && $wearableData->hat2 > 0) $hats[] = intval($wearableData->hat2);
        if (!empty($wearableData->hat3) && $wearableData->hat3 > 0) $hats[] = intval($wearableData->hat3);
        $items['hats'] = $hats;
        
        // Handle face - CRITICAL: face must be preserved as string including "0"
        if (isset($wearableData->face)) {
            $faceVal = $wearableData->face;
            if ($faceVal === null || $faceVal === '') {
                $items['face'] = 'default';
            } elseif (is_numeric($faceVal)) {
                // Keep as string "0" if numeric 0, so JS can load face/0/0.png
                $items['face'] = strval(intval($faceVal));
            } else {
                $items['face'] = strval($faceVal);
            }
        }
        
        // Other items
        $items['accessory'] = !empty($wearableData->accessory) ? intval($wearableData->accessory) : 0;
        $items['tshirt'] = !empty($wearableData->tshirt) ? intval($wearableData->tshirt) : 0;
        $items['shirt'] = !empty($wearableData->shirt) ? intval($wearableData->shirt) : 0;
        $items['pants'] = !empty($wearableData->pants) ? intval($wearableData->pants) : 0;
        $items['gear'] = !empty($wearableData->gear) ? intval($wearableData->gear) : 0;
    }
    
    return [
        'colors' => $colors,
        'items' => $items
    ];
}

function getActivityStatus($user) {
    if (!isset($user->expire)) return 'offline';
    return ($user->expire > time()) ? 'online' : 'offline';
}

function getLastOnlineDisplay($user) {
    if (!isset($user->expire)) return 'Offline';
    $diff = time() - $user->expire;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

// Fix: Check if timeAgo already exists before declaring it
if (!function_exists('timeAgo')) {
    function timeAgo($timestamp) {
        if (!$timestamp) return 'Never';
        return context::humanTiming($timestamp) . ' ago';
    }
}

function id2group($conn, $uid) {
    // ✅ Use prepared statements. Assumes $conn is a PDO object for consistency.
    try {
        $stmt = $conn->prepare("SELECT logo FROM user_groups WHERE id = :uid");
        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $f = $stmt->fetch(PDO::FETCH_OBJ);
        return $f ? $f->logo : null;
    } catch (PDOException $e) {
        error_log("id2group query failed: " . $e->getMessage());
        return null;
    }
}

// -----------------------------------------------------------------------------
// Helper: normalize avatar 'items' structure before JSON-encoding to HTML data-attrs.
// Ensures 'face' 0 is preserved as string "0", and hats is always an array.
// -----------------------------------------------------------------------------
function normalize_avatar_items_array($items) {
    // accept object or array
    if (is_object($items)) $items = (array)$items;
    if (!is_array($items)) $items = [];

    // ensure hats array
    $hats = [];
    if (isset($items['hats']) && is_array($items['hats'])) {
        foreach ($items['hats'] as $h) {
            if ($h && intval($h) > 0) $hats[] = intval($h);
        }
    } else {
        // support legacy hat1/hat2/hat3
        foreach (['hat1','hat2','hat3'] as $hk) {
            if (isset($items[$hk]) && intval($items[$hk]) > 0) $hats[] = intval($items[$hk]);
        }
    }
    $items['hats'] = $hats;

    // face: if property exists and is numeric, convert to string (preserve '0')
    if (array_key_exists('face', $items)) {
        $f = $items['face'];
        if ($f === null || $f === '') {
            $items['face'] = 'default';
        } elseif (is_numeric($f)) {
            $items['face'] = (string)intval($f); // preserves 0 as "0"
        } elseif (is_string($f)) {
            $items['face'] = $f;
        } else {
            $items['face'] = 'default';
        }
    } else {
        $items['face'] = 'default';
    }

    // ensure other item keys exist
    foreach (['accessory','tshirt','shirt','pants','gear'] as $key) {
        if (!array_key_exists($key, $items) || $items[$key] === null) $items[$key] = 0;
        // cast numeric strings to int where appropriate
        if (is_string($items[$key]) && ctype_digit($items[$key])) $items[$key] = intval($items[$key]);
    }

    return $items;
}
?>