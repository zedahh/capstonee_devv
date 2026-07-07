<?php
// SMS gateway credentials — NOT YET ACTIVE.
// sendSms() in includes/functions.php currently runs in simulated mode and does
// not read this file. At deployment, sign up for a Semaphore account
// (https://semaphore.co), fill in the API key below, and update sendSms()
// to call Semaphore's API using it.

define('SMS_API_KEY', 'FILL_IN_AT_DEPLOYMENT');