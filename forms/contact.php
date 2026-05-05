<?php
  declare(strict_types=1);

  header('X-Content-Type-Options: nosniff');

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
  }

  function clean_field(string $key, int $maxLength): string {
    $value = trim((string)($_POST[$key] ?? ''));
    $value = str_replace(["\r", "\n"], ' ', $value);
    $value = preg_replace('/[[:cntrl:]]/', '', $value) ?? '';
    return substr($value, 0, $maxLength);
  }

  /**
  * Requires the "PHP Email Form" library
  * The "PHP Email Form" library is available only in the pro version of the template
  * The library should be uploaded to: vendor/php-email-form/php-email-form.php
  * For more info and help: https://bootstrapmade.com/php-email-form/
  */

  $receiving_email_address = 'contact@intuitiveshield.com';

  $name = clean_field('name', 80);
  $email = filter_var(clean_field('email', 180), FILTER_VALIDATE_EMAIL);
  $subject = clean_field('subject', 140);
  $message = trim((string)($_POST['message'] ?? ''));
  $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message) ?? '';
  $message = substr($message, 0, 5000);

  if ($name === '' || $email === false || $subject === '' || $message === '') {
    http_response_code(422);
    exit('Invalid form submission');
  }

  if( file_exists($php_email_form = '../assets/vendor/php-email-form/php-email-form.php' )) {
    include( $php_email_form );
  } else {
    die( 'Unable to load the "PHP Email Form" Library!');
  }

  $contact = new PHP_Email_Form;
  $contact->ajax = true;
  
  $contact->to = $receiving_email_address;
  $contact->from_name = $name;
  $contact->from_email = $email;
  $contact->subject = $subject;

  // Uncomment below code if you want to use SMTP to send emails. You need to enter your correct SMTP credentials
  /*
  $contact->smtp = array(
    'host' => 'example.com',
    'username' => 'example',
    'password' => 'pass',
    'port' => '587'
  );
  */

  $contact->add_message($name, 'From');
  $contact->add_message($email, 'Email');
  isset($_POST['phone']) && $contact->add_message(clean_field('phone', 40), 'Phone');
  $contact->add_message($message, 'Message', 10);

  echo $contact->send();
?>
