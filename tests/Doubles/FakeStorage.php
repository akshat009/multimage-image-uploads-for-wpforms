<?php
/**
 * In-memory MIUWPF_Storage test double, keyed by token value.
 *
 * @package MIUWPF
 */

final class MIUWPF_Tests_Fake_Storage implements MIUWPF_Storage {

	/** @var array<string,int> Token value => size in bytes. */
	private $sizes = [];

	/** @var bool */
	public $cleanup_called = false;

	/** @var array|null Fixed return value for promote(), when set. */
	private $promote_result = null;

	/**
	 * Register a token as "existing" with a given size.
	 *
	 * @param string $token_value Token value.
	 * @param int    $size        Size in bytes.
	 */
	public function put( $token_value, $size ) {
		$this->sizes[ $token_value ] = $size;
	}

	public function set_promote_result( $result ) {
		$this->promote_result = $result;
	}

	public function store_upload( $tmp_name, MIUWPF_Token $token ) {
		$this->sizes[ $token->value() ] = filesize( $tmp_name ) ?: 0;

		return true;
	}

	public function exists( MIUWPF_Token $token ) {
		return isset( $this->sizes[ $token->value() ] );
	}

	public function size( MIUWPF_Token $token ) {
		return isset( $this->sizes[ $token->value() ] ) ? $this->sizes[ $token->value() ] : 0;
	}

	public function promote( MIUWPF_Token $token ) {
		return $this->promote_result;
	}

	public function cleanup_stale() {
		$this->cleanup_called = true;
	}
}
