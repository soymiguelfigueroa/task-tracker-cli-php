<?php

$option = $argv[1] ?? null;

if ($option) {
    switch ($option) {
        case 'add':
            echo "Adding task...\n";
            $description = $argv[2] ?? null;

            if ($description) {
                $filename = __DIR__ . DIRECTORY_SEPARATOR . 'tasks.json';
                $handle = fopen($filename, 'w+');
                $file_size = filesize($filename);

                if ($file_size > 0) {
                    $content = fread($handle, $file_size);
                    print_r($content);
                } else {
                    $date = date('Y-m-d', strtotime('now'));

                    $content = [
                        'id' => '1',
                        'description' => $description,
                        'status' => 'todo',
                        'createdAt' => $date,
                        'updatedAt' => $date,
                    ];

                    $content_encoded = json_encode($content);

                    fwrite($handle, $content_encoded);

                    echo 'The task has been added sucessfully!';
                }

                fclose($handle);
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
