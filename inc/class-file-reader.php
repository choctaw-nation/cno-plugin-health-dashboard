<?php
/**
 * File Reader Job
 *
 * @package ChoctawNation
 * @subpackage HealthDashboard
 */

namespace ChoctawNation\HealthDashboard;

use Error;
use SimpleXMLElement;
use WP_Filesystem_Base;

/**
 * File Reader Job
 */
class File_Reader {
	/**
	 * The filesystem instance to use for file operations
	 *
	 * @var callable $fs
	 */
	private $fs;

	public const TRANSIENT_KEY = 'cno_health_dashboard_latest_data';

	private const CACHE_LENGTH = 60 * 60; // 1 hour

	/**
	 * Constructor
	 *
	 * @param callable $fs The global function that returns the WP Filesystem instance, passed as a callable to allow for different timings of when the filesystem is initialized.
	 */
	public function __construct( callable $fs ) {
		$this->fs = $fs;
	}

	/**
	 * Gets the WP Filesystem instance, which is needed for file operations in the plugin.
	 *
	 * @throws Error The filesystem couldn't be initialized.
	 */
	private function get_filesystem(): WP_Filesystem_Base {
		$fs = call_user_func( $this->fs );

		if ( ! $fs instanceof WP_Filesystem_Base ) {
			throw new Error( 'Filesystem not available.' );
		}

		return $fs;
	}

	/**
	 * Reads the latest health data file and returns it as JSON or an array
	 */
	public function get_latest_data(): array {
		$cached_data = get_transient( self::TRANSIENT_KEY );
		if ( $cached_data ) {
			return $cached_data;
		}
		$health_data = $this->get_latest_health_data_file();
		$health_data = $this->sort_data( $health_data );
		set_transient( self::TRANSIENT_KEY, $health_data, self::CACHE_LENGTH );
		return $health_data;
	}

	/**
	 * Get the latest health data file
	 *
	 * @return SimpleXMLElement|false the latest health data file, `false` if it doesn't exist.
	 * @throws Error File doesn't exist or couldn't be parsed.
	 */
	private function get_latest_health_data_file(): SimpleXMLElement|false {
		$health_data_file = get_template_directory() . '/health/PublicHealth_CalcData.xml';
		$fs               = $this->get_filesystem();
		if ( ! $fs->exists( $health_data_file ) ) {
			throw new Error( 'Health data file not found!' );
		}
		$health_data = simplexml_load_file( $health_data_file );
		if ( false === $health_data ) {
			throw new Error( "Health data file couldn't be parsed!" );
		}
		return $health_data;
	}

	/**
	 * Sort data from the XML file into a useable array
	 *
	 * @param SimpleXMLElement $xml the XML Element to convert
	 * @return array the sorted data as an array
	 */
	private function sort_data( SimpleXMLElement $xml ): array {
		$data = array();
		foreach ( $xml->children() as $data_point ) {
			$week_of_year = null;
			$disease_data = null;
			foreach ( $data_point->children() as $property ) {
				$name = $property['Name']->__toString();
				if ( 'WeekOfYr' === $name ) {
					$week_of_year = $this->get_key_value_pairs( $property );
				} else {
					$disease_data = $this->get_key_value_pairs( $property );
				}
			}
			$data[] = array(
				'WeekOfYr' => $week_of_year,
				'diseases' => $disease_data,
			);
		}
		sort( $data );
		return $data;
	}

	/**
	 * Recursively destructures the XML data into key value pairs
	 *
	 * @param SimpleXMLElement $xml the XML Element to destructure
	 * @return array|string the key value pairs, or an error
	 */
	private function get_key_value_pairs( SimpleXMLElement $xml ) {
		$details  = array();
		$children = $xml->count();
		if ( 0 === $children ) {
			return $xml[0]->__toString();
		}
		foreach ( $xml->children() as $property ) {
			$name = $property['Name']->__toString();
			if ( 'Key' === $name ) {
				$key = $property[0]->__toString();
			} else {
				$value           = $this->get_key_value_pairs( $property );
				$details[ $key ] = 'NOVALUE' === $value ? null : $value;
			}
		}
		return $details;
	}

	/**
	 * Get the latest disease statuses as list elements
	 *
	 * @return array the disease statuses as an array of objects with `disease` and `comparison` properties
	 */
	public function get_latest_disease_statuses(): array {
		$health_data        = $this->get_latest_data();
		$diseases           = end( $health_data )['diseases'];
		$disease_rename_map = array(
			'FLU'   => 'Flu',
			'COVID' => 'COVID-19',
		);
		$data               = array();
		foreach ( $diseases as $disease => $disease_data ) {
			if ( array_key_exists( $disease, $disease_rename_map ) ) {
				$disease = $disease_rename_map[ $disease ];
			}
			$data[ $disease ] = $disease_data['Comparison'] ?? 'No data';
		}
		return $data;
	}
}
