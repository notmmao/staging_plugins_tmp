<?php

function order_uploads_merge_recursive($old, $new) {

    if (is_array($new)) {

        foreach ($new AS $product_id => $item_numbers) {

            foreach ($item_numbers AS $item_number => $upload_types) {

                foreach ($upload_types AS $upload_type => $file_numbers) {

                    foreach ($file_numbers AS $file_number => $data) {

                        $old[$product_id][$item_number][$upload_type][$file_number] = $data;

                    }

                }

            }

        }

    }

    return $old;

}

?>