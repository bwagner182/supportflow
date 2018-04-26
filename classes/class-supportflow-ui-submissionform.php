<?php

defined( 'ABSPATH' ) or die( "Cheatin' uh?" );

class SupportFlow_UI_SubmissionForm {

	public $messages = array();

	function __construct() {
		add_action( 'supportflow_after_setup_actions', array( $this, 'setup_actions' ) );
	}

	public function setup_actions() {
		add_action( 'init', array( $this, 'action_init_handle_form_submission' ) );

		add_shortcode( 'supportflow_submissionform', array( $this, 'shortcode_submissionform' ) );
	}

	/**
	 * Creates a form to be displayed on the WordPress front end for ticket submission.
	 * @return html HTML to create and display the form on the page.
	 */
	public function shortcode_submissionform() {
		?>
		<style type="text/css">
			.supportflow-message {
				font-weight: bold;
				color:       red;
			}
		</style>

		<div class="supportflow-submissionform">

			<?php if ( ! empty( $this->messages ) ) : ?>
				<p class="supportflow-message">
					<?php foreach ( $this->messages as $message ) : ?>
						<?php esc_html_e( $message ) ?>
						<br />
					<?php endforeach; ?>
				</p>
			<?php endif; ?>

			<form action="" method="POST">
				<input type="hidden" name="action" value="sf_create_ticket" />
				<?php wp_nonce_field( '_sf_create_ticket' ) ?>
				<?php // If the client is creating the ticket then this needs to be autofilled and hidden. ?>
				<p>
					<label for="client"><?php html_esc_e( 'Company Name', 'supportflow' ) ?>:</label>
					<br />
					<input type="text" required id="client" name="client" />
				</p>
<?php   /* ?>
				<p>
					<label for="email"><?php html_esc_e( 'Your E-Mail', 'supportflow' ) ?>:</label>
					<br />
					<input type="email" required id="email" name="email" />
				</p>
<?php //*/ ?>
				<p>
					<label for="title"><?php html_esc_e( 'Title', 'supportflow' ) ?>:</label>
					<br />
					<input type="text" required id="title" name="title" />
				</p>
				
				<?php // Create a field for Due Date which is hidden for clients ?>

				<p>
					<label for="description"><?php html_esc_e( 'Description', 'supportflow' ) ?>:</label>
					<br />
					<textarea required id="description" rows=5 name="description"></textarea>
				</p>

				<p>
					<label for="attachments"><?php html_esc_e( 'Attachments', 'supportflow' ); ?>:</label>
					<br />
					<input type="file" name="attachments" id="attachments" />
				</p>

				<p>
					<label for="page-url"><?php html_esc_e( 'Page URL', 'supportflow' ); ?>:</label>
					<br />
					<input type="text" required name="page-url" id="page-url" />
				</p>

				<p>
					<input type="submit" value="<?php html_esc_e( 'Submit', 'supportflow' ) ?>" />
				</p>

			</form>
		</div>
	<?php
	}

	public function action_init_handle_form_submission() {

		if (
			! isset( $_POST['action'], $_POST['_wpnonce'] ) ||
			! 'sf_create_ticket' == $_POST['action'] ||
			! wp_verify_nonce( $_POST['_wpnonce'], '_sf_create_ticket' )
		) {
			return;
		}

		if ( empty( $_POST['client'] ) ) {
			$this->messages[] = __( 'The company name field is required.', 'supportflow' );
		}
/*
		if ( empty( $_POST['email'] ) ) {
			$this->messages[] = __( 'The email field is required.', 'supportflow' );

		} elseif ( ! is_email( $_POST['email'] ) ) {
			$this->messages[] = __( 'Please enter a valid e-mail address.', 'supportflow' );
		}
*/
		if ( empty( $_POST['title'] ) ) {
			$this->messages[] = __( 'The title field is required.', 'supportflow' );
		}

		if ( empty( $_POST['description'] ) ) {
			$this->messages[] = __( 'You must enter a description.', 'supportflow' );
		}

		if (empty( $_POST['page-url'] ) ) {
			$this->messages[] = __( 'You must enter a page link.', 'supportflow' );
		}

		if ( ! empty( $this->messages ) ) {
			return;
		}

		// Load required file
		require_once( SupportFlow()->plugin_dir . 'classes/class-supportflow-admin.php' );

		$ticket_id = SupportFlow()->create_ticket(
			array(
				'client'             => $_POST['client'],
				'title'              => $_POST['title'],
				'description'        => $_POST['description'],
				//'reply_author'       => $_POST['fullname'],
				'client_name'        => $_POST['client-name'],
				// 'reply_author_email' => $_POST['email'],
				// 'customer_email'   => array( $_POST['email'] ),
				'attachments'		 => $_POST['attachments'],
				'page_url'           => $_POST['page-url'],
			)
		);

		if ( is_wp_error( $ticket_id ) ) {
			$this->messages[] = __( 'There is an unknown error while submitting the form. Please try again later.', 'supportflow' );

			return;
		}

		$this->messages[] = __( 'Form submitted successfully', 'supportflow' );
	}
}

SupportFlow()->extend->ui->submissionform = new SupportFlow_UI_SubmissionForm();
