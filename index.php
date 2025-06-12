<?php

$option = $argv[1] ?? null;

if ($option) {
    switch ($option) {
        case 'add':
            echo "Adding task...\n";
            $description = $argv[2] ?? null;

            if ($description) {
                $filename = __DIR__ . DIRECTORY_SEPARATOR . 'tasks.json';
                $file_size = filesize($filename);

                if ($file_size > 0) {
                    $handle = fopen($filename, 'r+');
                    $content = fread($handle, $file_size);
                    fclose($handle);
                    $content_decoded = json_decode($content, true);
                    $last_task = end($content_decoded);
                    $last_id = intval($last_task['id']) + 1;
                    $current_date = date('Y-m-d', strtotime('now'));
                    $content_decoded[] = (object) [
                        'id' => $last_id,
                        'description' => $description,
                        'status' => 'todo',
                        'createdAt' => $current_date,
                        'updatedAt' => $current_date,
                    ];
                    $content_encoded = json_encode($content_decoded, JSON_FORCE_OBJECT);
                    file_put_contents($filename, $content_encoded);

                    echo "The task has been added sucessfully!\n";
                } else {
                    $handle = fopen($filename, 'w+');
                    $current_date = date('Y-m-d', strtotime('now'));

                    $content[] = [
                        'id' => 1,
                        'description' => $description,
                        'status' => 'todo',
                        'createdAt' => $current_date,
                        'updatedAt' => $current_date,
                    ];

                    $content_encoded = json_encode($content);

                    fwrite($handle, $content_encoded);
                    fclose($handle);

                    echo "The task has been added sucessfully!\n";
                }
            } else {
                echo "You need to enter the task description\n";
            }
            break;

        case 'update':
            echo "Updating task\n";
            break;

        case 'delete':
            echo "Deleting task\n";
            break;

        case 'mark':
            $sub_option = $argv[2] ?? null;

            if ($sub_option == 'in-progress') {
                echo "Mark in progress\n";
            } elseif ($sub_option == 'done') {
                echo "Mark done\n";
            } else {
                echo "The option is not valid\n";
            }
            break;

        case 'list':
            $sub_option = $argv[2] ?? null;

            if ($sub_option == 'in-progress') {
                echo "Listing in progress tasks\n";
            } elseif ($sub_option == 'done') {
                echo "Listing done tasks\n";
            } elseif ($sub_option == 'todo') {
                echo "Listing not done tasks\n";
            } else {
                echo "Listing all tasks\n";
            }
            break;
        
        default:
            echo "The option is not valid\n";
            break;
    }
} else {
    echo "The option is not valid\n";
}
