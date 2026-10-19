<?php
/**
 * Value object representing a checkout field definition.
 *
 * @package WPCalibrate\CheckoutStudio
 */

declare(strict_types=1);

namespace WPCalibrate\CheckoutStudio\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FieldDefinition
 */
final class FieldDefinition {

	private string $id;
	private string $key;
	private bool $is_core;
	private string $type;
	private string $label;
	private string $placeholder;
	private string $description;
	private string $section;
	private bool $enabled;
	private bool $required;
	private int $priority;
	private string $width;
	private string $css_classes;
	private string $autocomplete;
	private string $default_value;
	/** @var array<int, array<string, string>> */
	private array $options;
	/** @var array<string, mixed> */
	private array $validation;
	/** @var array<string, mixed> */
	private array $conditions;
	/** @var array<string, bool> */
	private array $display;

	/**
	 * Constructor.
	 *
	 * @param array<string, mixed> $data
	 */
	public function __construct( array $data ) {
		$this->id            = (string) ( $data['id'] ?? '' );
		$this->key           = (string) ( $data['key'] ?? '' );
		$this->is_core       = ! empty( $data['is_core'] );
		$this->type          = (string) ( $data['type'] ?? 'text' );
		$this->label         = (string) ( $data['label'] ?? '' );
		$this->placeholder   = (string) ( $data['placeholder'] ?? '' );
		$this->description   = (string) ( $data['description'] ?? '' );
		$this->section       = (string) ( $data['section'] ?? 'billing' );
		$this->enabled       = ! empty( $data['enabled'] );
		$this->required      = ! empty( $data['required'] );
		$this->priority      = isset( $data['priority'] ) ? (int) $data['priority'] : 10;
		$this->width         = (string) ( $data['width'] ?? 'full' );
		$this->css_classes   = (string) ( $data['css_classes'] ?? '' );
		$this->autocomplete  = (string) ( $data['autocomplete'] ?? '' );
		$this->default_value = (string) ( $data['default_value'] ?? '' );
		$this->options       = is_array( $data['options'] ?? null ) ? $data['options'] : [];
		$this->validation    = is_array( $data['validation'] ?? null ) ? $data['validation'] : [];
		$this->conditions    = is_array( $data['conditions'] ?? null ) ? $data['conditions'] : [];
		$this->display       = is_array( $data['display'] ?? null ) ? $data['display'] : [
			'admin'    => true,
			'customer' => true,
			'emails'   => true,
		];
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_key(): string {
		return $this->key;
	}

	public function is_core(): bool {
		return $this->is_core;
	}

	public function get_type(): string {
		return $this->type;
	}

	public function get_label(): string {
		return $this->label;
	}

	public function get_placeholder(): string {
		return $this->placeholder;
	}

	public function get_description(): string {
		return $this->description;
	}

	public function get_section(): string {
		return $this->section;
	}

	public function is_enabled(): bool {
		return $this->enabled;
	}

	public function is_required(): bool {
		return $this->required;
	}

	public function get_priority(): int {
		return $this->priority;
	}

	public function get_width(): string {
		return $this->width;
	}

	public function get_css_classes(): string {
		return $this->css_classes;
	}

	public function get_autocomplete(): string {
		return $this->autocomplete;
	}

	public function get_default_value(): string {
		return $this->default_value;
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	public function get_options(): array {
		return $this->options;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_validation(): array {
		return $this->validation;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function get_conditions(): array {
		return $this->conditions;
	}

	/**
	 * @return array<string, bool>
	 */
	public function get_display(): array {
		return $this->display;
	}

	public function should_show_in_order_admin(): bool {
		return ! empty( $this->display['admin'] );
	}

	public function should_show_in_customer_view(): bool {
		return ! empty( $this->display['customer'] );
	}

	public function should_show_in_emails(): bool {
		return ! empty( $this->display['emails'] );
	}

	/**
	 * Convert to array representation.
	 *
	 * @return array<string, mixed>
	 */
	public function to_array(): array {
		return [
			'id'           => $this->id,
			'key'          => $this->key,
			'is_core'      => $this->is_core,
			'type'         => $this->type,
			'label'        => $this->label,
			'placeholder'  => $this->placeholder,
			'description'  => $this->description,
			'section'      => $this->section,
			'enabled'      => $this->enabled,
			'required'     => $this->required,
			'priority'     => $this->priority,
			'width'        => $this->width,
			'css_classes'  => $this->css_classes,
			'autocomplete' => $this->autocomplete,
			'default_value'=> $this->default_value,
			'options'      => $this->options,
			'validation'   => $this->validation,
			'conditions'   => $this->conditions,
			'display'      => $this->display,
		];
	}
}
