<?php
/**
 * Tests for MIUWPF_Email_Attachments.
 *
 * @package MIUWPF
 */

use Brain\Monkey\Filters;

final class EmailAttachmentsTest extends MIUWPF_TestCase {

	public function test_register_hooks_adds_the_email_atts_filter() {

		Filters\expectAdded( 'wpforms_entry_email_atts' )->once();

		( new MIUWPF_Email_Attachments() )->register_hooks();

		// Brain Monkey's expectation is verified in tear_down(); this just
		// keeps PHPUnit from flagging the test as performing no assertions.
		$this->addToAssertionCount( 1 );
	}

	public function test_filter_email_returns_email_unmodified_when_nothing_collected() {

		$email      = [ 'subject' => 'Hi' ];
		$attachments = new MIUWPF_Email_Attachments();

		$result = $attachments->filter_email( $email, [], [], [] );

		$this->assertSame( $email, $result );
	}

	public function test_filter_email_adds_collected_paths_as_attachments() {

		$attachments = new MIUWPF_Email_Attachments();
		$attachments->collect( 1, [ '/uploads/a.jpg', '/uploads/b.jpg' ] );

		$result = $attachments->filter_email( [ 'subject' => 'Hi' ], [], [], [] );

		$this->assertSame( [ '/uploads/a.jpg', '/uploads/b.jpg' ], $result['attachments'] );
		$this->assertSame( 'Hi', $result['subject'] );
	}

	public function test_filter_email_merges_paths_collected_from_multiple_fields() {

		$attachments = new MIUWPF_Email_Attachments();
		$attachments->collect( 1, [ '/uploads/a.jpg' ] );
		$attachments->collect( 2, [ '/uploads/b.jpg', '/uploads/c.jpg' ] );

		$result = $attachments->filter_email( [], [], [], [] );

		$this->assertCount( 3, $result['attachments'] );
		$this->assertContains( '/uploads/a.jpg', $result['attachments'] );
		$this->assertContains( '/uploads/b.jpg', $result['attachments'] );
		$this->assertContains( '/uploads/c.jpg', $result['attachments'] );
	}

	public function test_filter_email_appends_to_pre_existing_attachments() {

		$attachments = new MIUWPF_Email_Attachments();
		$attachments->collect( 1, [ '/uploads/new.jpg' ] );

		$result = $attachments->filter_email( [ 'attachments' => [ '/uploads/existing.pdf' ] ], [], [], [] );

		$this->assertSame( [ '/uploads/existing.pdf', '/uploads/new.jpg' ], $result['attachments'] );
	}

	public function test_collecting_again_for_the_same_field_id_replaces_rather_than_merges() {

		$attachments = new MIUWPF_Email_Attachments();
		$attachments->collect( 1, [ '/uploads/first.jpg' ] );
		$attachments->collect( 1, [ '/uploads/second.jpg' ] );

		$result = $attachments->filter_email( [], [], [], [] );

		$this->assertSame( [ '/uploads/second.jpg' ], $result['attachments'] );
	}

	public function test_collect_normalizes_the_field_id_with_absint() {

		$attachments = new MIUWPF_Email_Attachments();
		$attachments->collect( '5', [ '/uploads/a.jpg' ] );
		$attachments->collect( 5, [ '/uploads/b.jpg' ] );

		$result = $attachments->filter_email( [], [], [], [] );

		// Both calls resolve to the same integer key, so the second replaces the first.
		$this->assertSame( [ '/uploads/b.jpg' ], $result['attachments'] );
	}

	public function test_collecting_an_empty_array_still_results_in_no_attachments_key() {

		$attachments = new MIUWPF_Email_Attachments();
		$attachments->collect( 1, [] );

		$result = $attachments->filter_email( [ 'subject' => 'Hi' ], [], [], [] );

		$this->assertArrayNotHasKey( 'attachments', $result );
	}
}
