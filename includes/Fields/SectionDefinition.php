<?php
/**
 * Value object representing a checkout section definition.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SectionDefinition
 */
final class SectionDefinition {

	private string $id;
	private string $key;
	private string $title;
	private string $description;
	private bool $enabled;
	private int $priority;
	private bool $is_core;
	/** @var array<int, string> */
	private array $supported_renderers;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $data
	 */
	public function __construct( array $data ) {
		$this->id                  = (string) ( $data['id'] ?? '' );
		$this->key                 = (string) ( $data['key'] ?? '' );
		$this->title               = (string) ( $data['title'] ?? '' );
		$this->description         = (string) ( $data['description'] ?? '' );
		$this->enabled             = ! empty( $data['enabled'] );
		$this->priority            = isset( $data['priority'] ) ? (int) $data['priority'] : 10;
		$this->is_core             = ! empty( $data['is_core'] );
		$this->supported_renderers = is_array( $data['supported_renderers'] ?? null ) ? $data['supported_renderers'] : [ 'classic', 'blocks' ];
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_key(): string {
		return $this->key;
	}

	public function get_title(): string {
		return $this->title;
	}

	public function get_description(): string {
		return $this->description;
	}

	public function is_enabled(): bool {
		return $this->enabled;
	}

	public function get_priority(): int {
		return $this->priority;
	}

	public function is_core(): bool {
		return $this->is_core;
	}

	/**
	 * @return array<int, string>
	 */
	public function get_supported_renderers(): array {
		return $this->supported_renderers;
	}

	/**
	 * Convert to array representation.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'id'                  => $this->id,
			'key'                 => $this->key,
			'title'               => $this->title,
			'description'         => $this->description,
			'enabled'             => $this->enabled,
			'priority'            => $this->priority,
			'is_core'             => $this->is_core,
			'supported_renderers' => $this->supported_renderers,
		];
	}
}
