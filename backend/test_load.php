<?php
require __DIR__ . '/vendor/autoload.php';
use Tests\Feature\BusinessWorkflowTest;
if (class_exists(BusinessWorkflowTest::class)) {
    echo "SUCCESS: Class Tests\\Feature\\BusinessWorkflowTest found.\n";
} else {
    echo "FAILURE: Class Tests\\Feature\\BusinessWorkflowTest NOT found.\n";
}
