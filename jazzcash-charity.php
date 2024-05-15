<?php
/*
Plugin Name: JazzCash-Charity
Description: This is a Plugin for JazzCash-Charity and save the submitted data into a custom table.
Version: 1.0
Author: Abdul Rafay
*/
include_once(plugin_dir_path(__FILE__) . 'config.php');
include_once(plugin_dir_path(__FILE__) . 'jazzcashApi.php');
// Create custom table on plugin activation
register_activation_hook(__FILE__, 'create_jazzcash_charity_table');

function create_jazzcash_charity_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'jazzcash_charity';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        amount VARCHAR(255) NOT NULL,
        address VARCHAR(255) DEFAULT NULL,
        address2 VARCHAR(255) DEFAULT NULL,
        city VARCHAR(255) DEFAULT NULL,
        state VARCHAR(255) DEFAULT NULL,
        country VARCHAR(255) DEFAULT NULL,
        postal_code VARCHAR(255) DEFAULT NULL,
        phone_no VARCHAR(255) DEFAULT NULL,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

//add form shortcode
add_shortcode( 'jazzcash_charity_form', 'jazzcash_charity_form' );
function jazzcash_charity_form() {
    ob_start(); ?>
<style>
    .container {
        max-width: 500px;
        margin: 0 auto;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .label-text {
        font-weight: bold;
    }
    .input-field {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .radio-label {
        margin-right: 10px;
    }
    .payment-details {
        display: none;
    }
    .show-payment-details .payment-details {
        display: block;
    }
    .submit-button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
</style>

<div class="form-container">
<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
    <input type="hidden" name="action" value="process_jazzcash_charity_form">
    <?php wp_nonce_field('jazzcash_charity_form_action', 'jazzcash_charity_form_nonce'); ?>
    <!-- Use unique nonce field action name to avoid conflicts -->
    <div class="form-group">
        <label class="label-text">Donation Amount:</label>
        <label class="radio-label"><input type="radio" name="amount" value="100"> Rs.100</label>
        <label class="radio-label"><input type="radio" name="amount" value="500"> Rs.500</label>
        <label class="radio-label"><input type="radio" name="amount" value="1000"> Rs.1000</label>
        <label class="radio-label"><input type="radio" name="amount" value="5000"> Rs.5000</label>
        <label class="radio-label"><input type="radio" name="amount" value="10000"> Rs.10000</label>
        <label class="radio-label"><input type="radio" name="amount" value="custom" id="customAmountRadio"> Custom</label>
        <input class="input-field" type="number" id="customAmount" name="customAmount" style="display: none;">
    </div>
    <div class="form-group">
        <label class="label-text" for="firstName">First Name:</label>
        <input class="input-field" type="text" id="firstName" name="firstName">
    </div>
    <div class="form-group">
        <label class="label-text" for="lastName">Last Name:</label>
        <input class="input-field" type="text" id="lastName" name="lastName">
    </div>
    <div class="form-group">
        <label class="label-text" for="email">Email:</label>
        <input class="input-field" type="email" id="email" name="email">
    </div>
    <div class="form-group">
        <label class="label-text" for="address">Address:</label>
        <input class="input-field" type="text" id="address" name="address">
    </div>
    <div class="form-group">
        <label class="label-text" for="address2">Address 2:</label>
        <input class="input-field" type="text" id="address2" name="address2">
    </div>
    <div class="form-group">
        <label class="label-text" for="city">City:</label>
        <input class="input-field" type="text" id="city" name="city">
    </div>
    <div class="form-group">
        <label class="label-text" for="state">State:</label>
        <input class="input-field" type="text" id="state" name="state">
    </div>
    <div class="form-group">
        <label class="label-text" for="postalCode">Postal Code:</label>
        <input class="input-field" type="text" id="postalCode" name="postalCode">
    </div>
    <div class="form-group">
        <label class="label-text" for="country">Country:</label>
        <input class="input-field" type="text" id="country" name="country" value="Pakistan"> <!-- Default to Pakistan -->
    </div>
    <div class="form-group">
        <label class="label-text" for="phone">Phone Number:</label>
        <input class="input-field" type="tel" id="phone" name="phone">
    </div>
    <div class="form-group">
        <label class="label-text" for="paymentMethod">Payment Method:</label>
        <label class="radio-label"><input type="radio" name="paymentMethod" value="jazzcashCard" class="payment-method"> Card</label>
        <label class="radio-label"><input type="radio" name="paymentMethod" value="jazzcashMobile" class="payment-method"> Jazzcash</label>
    </div>
    <div class="form-group payment-details payment-details-card" id="card-section">
        <label class="label-text" for="cardNumber">Card Number:</label>
        <input class="input-field" type="text" id="cardNumber" name="cardNumber">
        <label class="label-text" for="cvv">CVV:</label>
        <input class="input-field" type="text" id="cvv" name="cvv">
        <label class="label-text" for="expiryMonth">Expiry Month:</label>
        <input class="input-field" type="text" id="expiryMonth" name="expiryMonth">
        <label class="label-text" for="expiryYear">Expiry Year:</label>
        <input class="input-field" type="text" id="expiryYear" name="expiryYear">
    </div>
    <div class="form-group payment-details payment-details-jazzcash" id="jazzcash-section">
        <label class="label-text" for="mobileNumber">Mobile Number:</label>
        <input class="input-field" type="text" id="mobileNumber" name="mobileNumber">
        <label class="label-text" for="cnic">Last 6 Digits of CNIC:</label>
        <input class="input-field" type="text" id="cnic" name="cnic">
    </div>
    <button type="submit" class="submit-button">Submit</button>
</form>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const customAmountRadio = document.getElementById("customAmountRadio");
        const customAmountField = document.getElementById("customAmount");

        customAmountRadio.addEventListener("change", function() {
            if (this.checked) {
                customAmountField.style.display = "block";
            } else {
                customAmountField.style.display = "none";
            }
        });

        const paymentMethodRadios = document.querySelectorAll(".payment-method");
        const paymentDetailsCard = document.querySelector(".payment-details-card");
        const paymentDetailsJazzcash = document.querySelector(".payment-details-jazzcash");

        paymentMethodRadios.forEach(function(radio) {
            radio.addEventListener("change", function() {
            // console.log(this.value);
                if (this.value === "jazzcashCard") {
                    paymentDetailsCard.style.display = "block";
                    paymentDetailsJazzcash.style.display = "none";
                } else if (this.value === "jazzcashMobile") {
                    paymentDetailsCard.style.display = "none";
                    paymentDetailsJazzcash.style.display = "block";
                }
            });
        });
    });
</script>
</div>

    <?php
    return ob_get_clean();
}
//handle form submittion 
add_action( 'admin_post_process_jazzcash_charity_form', 'process_jazzcash_charity_form' );
add_action( 'admin_post_nopriv_process_jazzcash_charity_form', 'process_jazzcash_charity_form' );
function process_jazzcash_charity_form() {
    if (!isset($_POST['jazzcash_charity_form_nonce']) || !wp_verify_nonce($_POST['jazzcash_charity_form_nonce'], 'jazzcash_charity_form_action')) {
        wp_die('Security check failed');
    }
    // print_r($_POST);
    if (isset($_POST['firstName']) && isset($_POST['email'])) {
        $referer = wp_get_referer();
        global $wpdb;
        $table_name = $wpdb->prefix . 'jazzcash_charity';
        if(isset($_POST['amount']) && $_POST['amount'] == 'custom') {
            $amount = $_POST['customAmount'];
        } else {
            $amount = $_POST['amount'];
        }
        $data = array(
            'first_name' => $_POST['firstName'],
            'last_name' => $_POST['lastName'],
            'address' => $_POST['address'],
            'address2' => $_POST['address2'],
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'country' => $_POST['country'],
            'postal_code' => $_POST['postalCode'],
            'phone_no' => $_POST['phone'],
            'amount' => $amount,
            'email' => $_POST['email'],

        );
        $jcData = array(
            'jazz_cash_no' => $_POST['mobileNumber'],
            'cnic_digits' => $_POST['cnic'],
            'price' => $amount,
            'paymentMethod' => $_POST['paymentMethod'],
            'ccNo'=> $_POST['cardNumber'],
            'expMonth'=> $_POST['expiryMonth'],
            'expYear'=> $_POST['expiryYear'],
            'cvv'=> $_POST['cvv'],
        );
        $format = array('%s', '%s');
        $result = $wpdb->insert($table_name, $data, $format);
        if ($result) {
            if ($referer) {
                wp_redirect($referer);
                exit;
            }
            exit;
        } else {
            echo "Error submitting form.";
        }
    }
}
//add custom admin menu
add_action( 'admin_menu', 'jazzcash_charity_menu' );
function jazzcash_charity_menu() {
    add_menu_page(
        'JazzCash-Charity',
        'JazzCash-Charity',
        'manage_options',
        'jazzcash_charity',
        'jazzcash_charity_admin_page'
    );
}

//add custom admin page
function jazzcash_charity_admin_page() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'jazzcash_charity';
    
    // Pagination variables
    $per_page = 1;
    $current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
    $offset = ( $current_page - 1 ) * $per_page;

    $total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );
    $submissions = $wpdb->get_results( "SELECT * FROM $table_name LIMIT $per_page OFFSET $offset" );

    if (empty($submissions)) {
        echo '<div class="wrap">';
        echo '<h2>Charity Details</h2>';
        echo '<p>No data found.</p>';
        echo '</div>';
        return;
    }
    ?>
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
        }
        .pagination li {
            margin: 0 5px;
        }
        .pagination li a {
            display: block;
            padding: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
            text-decoration: none;
            color: #333;
        }
        .pagination li.active a {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .pagination li.disabled a {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
    <div class="wrap">
        <h2>Charity Details</h2>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Amount</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Address</th>
                    <th scope="col">City</th>
                    <th scope="col">State</th>
                    <th scope="col">Country</th>
                    <th scope="col">Postal Code</th>
                    <th scope="col">Submission Date</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission) : ?>
                    <tr>
                        <td><?php echo esc_html($submission->first_name); ?></td>
                        <td><?php echo esc_html($submission->last_name); ?></td>
                        <td><?php echo esc_html($submission->email); ?></td>
                        <td><?php echo esc_html($submission->amount); ?></td>
                        <td><?php echo esc_html($submission->phone_no); ?></td>
                        <td><?php echo esc_html($submission->address); ?></td>
                        <td><?php echo esc_html($submission->city); ?></td>
                        <td><?php echo esc_html($submission->state); ?></td>
                        <td><?php echo esc_html($submission->country); ?></td>
                        <td><?php echo esc_html($submission->postal_code); ?></td>
                        <td><?php echo esc_html($submission->submission_date); ?></td>
                        <td>
                            <form method="post" action="admin-post.php">
                                <input type="hidden" name="action" value="delete_jazzcash_charity_submission">
                                <input type="hidden" name="submission_id" value="<?php echo esc_attr($submission->id); ?>">
                                <input type="submit" value="Delete">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        // Pagination links
        $total_pages = ceil( $total_items / $per_page );
        if ( $total_pages > 1 ) {
            ?>
            <ul class="pagination">
                <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo esc_url( add_query_arg( 'paged', $current_page - 1 ) ); ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                    <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo esc_url( add_query_arg( 'paged', $i ) ); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo esc_url( add_query_arg( 'paged', $current_page + 1 ) ); ?>">Next</a>
                </li>
            </ul>
            <?php
        }
        ?>
    </div>
    <?php
}





