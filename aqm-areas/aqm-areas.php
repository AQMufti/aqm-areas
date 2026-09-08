<?php
/**
 * Plugin Name: AQM Areas We Serve
 * Description: One editable list of the areas AQ covers, rendered anywhere with [aqm_areas]. Edit once, updates every page. Converted from a must-use plugin on 8 Sep 2026 so it can update itself from GitHub releases like every other AQM plugin.
 * Version:     1.4.0
 * Author:      A. Q. Mufti
 * Plugin URI:  https://github.com/AQMufti/aqm-areas
 * License:     GPL-2.0-or-later
 *
 * WHY THIS EXISTS
 * ===============
 *
 * Written 4 Sep 2026.
 *
 * The same 14 areas were built by hand as 14 Elementor columns on the homepage
 * AND again on /perfect-home-finder/. Two copies of one list means every change
 * is two edits, and the two drift apart - which is exactly what happened:
 *
 *   - the homepage tiles linked to AQ's own rem.ax searches; the Perfect Home
 *     Finder tiles linked ONLY to roundroomboston.com, a Boston company
 *   - one page reads "Areas We Cover", the other "Areas We Covered"
 *   - Perfect Home Finder split one city across two tiles: "Brampton, St" and
 *     "Catherines"
 *
 * AQ, 4 Sep 2026: "Make it so that any change on the Areas We Serve on the Home
 * page automatically updates on perfect-home-finder and wherever it is repeated
 * on the website."
 *
 * So the list lives in ONE option, edited on ONE screen, and every page renders
 * it with [aqm_areas]. It also deletes a large chunk of Elementor from two
 * pages, which the Elementor exit wants anyway.
 */

defined( 'ABSPATH' ) || exit;

/*
 * THE UPDATER IS CONSTRUCTED FIRST, DELIBERATELY.
 *
 * In 1.1.0 and 1.2.0 the conversion guard ran BEFORE this block and returned
 * early, so AQM_Updater was never constructed - which removed the "Check for
 * updates" link from this plugin's row and left no way to update it except a
 * manual zip upload. A guard that disables the thing that would have fixed the
 * guard is a trap. Registering the updater first costs nothing (it only adds
 * filters) and keeps the plugin repairable however badly the rest goes wrong.
 */
define( 'AQM_AREAS_FILE', __FILE__ );
define( 'AQM_AREAS_VERSION', '1.4.0' );
define( 'AQM_AREAS_GITHUB_REPO', 'AQMufti/aqm-areas' );

// Shared GitHub-release updater - identical mechanism in every AQM plugin.
require_once __DIR__ . '/aqm-updater.php';
new AQM_Updater(
	__FILE__,
	AQM_AREAS_VERSION,
	AQM_AREAS_GITHUB_REPO,
	'AQM Areas We Serve',
	'One editable list of the areas AQ covers, rendered anywhere with [aqm_areas].'
);

/*
 * CONVERSION GUARD - remove after the mu-plugin copy is gone.
 *
 * This was a must-use plugin until 8 Sep 2026. mu-plugins load BEFORE regular
 * plugins, so if the old mu-plugins/aqm-areas.php is still on the server every
 * function below would be declared twice and the site would fatal. Bail out
 * instead, and say why on the Plugins screen.
 */
if ( file_exists( ( defined( 'WPMU_PLUGIN_DIR' ) ? WPMU_PLUGIN_DIR : WP_CONTENT_DIR . '/mu-plugins' ) . '/aqm-areas.php' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p><strong>AQM Areas We Serve</strong> is not running. '
				. 'The old must-use copy at <code>wp-content/mu-plugins/aqm-areas.php</code> is still on the server '
				. 'and loads first. Delete that file, then reload this page.</p></div>';
		}
	);
	return;
}

/*
 * Belt and braces. The mu-plugin file is gone, but something else has already
 * declared our symbols - so loading on would be a fatal redeclare. Bail out
 * quietly and report WHERE it came from, using reflection, rather than blaming
 * a file that is not there.
 *
 * Version 1.1.0 tested only function_exists( 'aqm_areas_default_list' ), which is a
 * PROXY for "the old file is still present" rather than the thing itself. When
 * the four files were deleted on 8 Sep 2026 the notices kept firing, because
 * the proxy was answering a different question. Test the actual condition.
 */
if ( function_exists( 'aqm_areas_default_list' ) ) {
	add_action(
		'admin_notices',
		function () {

			$rel = function ( $path ) {
				return esc_html( str_replace( ABSPATH, '', (string) $path ) );
			};

			// Where the symbol was FIRST declared.
			$first = 'unknown';
			try {
				$r     = new ReflectionFunction( 'aqm_areas_default_list' );
				$first = $rel( $r->getFileName() );
			} catch ( Exception $e ) {
				unset( $e );
			}

			// Where THIS copy lives. If these two differ there are two installs;
			// if they match, one file is being executed twice, which include_once
			// should make impossible - and that is the thing worth knowing.
			$self = $rel( AQM_AREAS_FILE );

			// Who pulled us in this time.
			$chain = array();
			foreach ( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) as $frame ) {
				if ( ! empty( $frame['file'] ) ) {
					$chain[] = $rel( $frame['file'] ) . ':' . ( isset( $frame['line'] ) ? (int) $frame['line'] : 0 );
				}
			}
			$chain = array_slice( array_unique( $chain ), 0, 6 );

			$sandbox = defined( 'WP_SANDBOX_SCRAPING' ) ? 'yes' : 'no';

			echo '<div class="notice notice-warning"><p><strong>AQM Areas We Serve</strong> stood down to avoid a duplicate declaration.</p>'
				. '<p style="font-family:monospace;font-size:12px;line-height:1.7">'
				. 'this copy&nbsp;&nbsp;: ' . $self . '<br>'
				. 'declared in: ' . $first . '<br>'
				. 'same file&nbsp;&nbsp;&nbsp;: ' . ( $self === $first ? 'YES - one file executed twice' : 'no - two separate installs' ) . '<br>'
				. 'sandbox&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ' . $sandbox . '<br>'
				. 'included by: ' . esc_html( implode( ' &lt; ', $chain ) )
				. '</p></div>';
		}
	);
	return;
}

/**
 * The list, seeded from the homepage as it stood on 4 Sep 2026.
 *
 * Each row: name | search URL | image URL
 *
 * ⚠ The rem.ax URLs are AQ's own RE/MAX searches and are on the NEVER_TOUCH
 * list - they are copied here exactly, never rewritten.
 */
function aqm_areas_default_list() {
	$u = 'https://aqmuftirealty.com/wp-content/uploads/2025/01/';
	return implode(
		"\n",
		array(
			'Mississauga | https://rem.ax/SearchMississauga | ' . $u . '7xm.xyz246213.jpg',
			'Oakville | https://rem.ax/SearchOakville | ' . $u . '7xm.xyz976787.jpg',
			'Milton | https://rem.ax/SearchMilton | ' . $u . '7xm.xyz345417.jpg',
			'Waterloo | https://rem.ax/SearchWaterloo | ' . $u . '7xm.xyz999966.jpg',
			'Ajax | https://rem.ax/SearchAjax | ' . $u . 'image-1.webp',
			'Toronto | https://rem.ax/SearchToronto | ' . $u . 'licensed-image.jpg',
			'Oshawa | https://rem.ax/SearchOshawa | ' . $u . 'IMG_20230401_105829.jpg',
			'Niagara Falls | https://rem.ax/SearchNiagaraFalls | ' . $u . '7xm.xyz137846.jpg',
			'Pickering | https://rem.ax/SearchPickering | ' . $u . '7xm.xyz264858.jpg',
			'Kitchener | https://rem.ax/SearchKitchener | ' . $u . 'Kitchener_Night.jpg',
			'Cambridge | https://rem.ax/SearchCambridge | ' . $u . 'Untitled-design-32.jpg',
			'Brampton | https://rem.ax/SearchBrampton | ' . $u . 'Brampton.webp',
			// ⚠ This image is the Catherine Palace in ST PETERSBURG, RUSSIA -
			// matched on the name "Catherine". It illustrates an Ontario city
			// with a Russian palace. Replace it.
			'St. Catharines | https://rem.ax/SearchStCatharines | ' . $u . 'catherine-palace-in-saint-petersburg.jpg',
			'Hamilton | https://rem.ax/SearchHamilton | ' . $u . 'Hamilton-Ont.webp',
		)
	);
}

/** Settings, with the defaults that reproduce the current homepage block. */
function aqm_areas_settings() {
	$defaults = array(
		'list'      => aqm_areas_default_list(),
		'heading'   => 'Areas We Serve',
		'eyebrow'   => '',
		'columns'   => 7,
		'new_tab'   => 1,
	);
	$saved = get_option( 'aqm_areas_settings', array() );
	return array_merge( $defaults, is_array( $saved ) ? $saved : array() );
}

/**
 * Parse the textarea into rows.
 *
 * One area per line: Name | URL | Image URL
 * Blank lines and lines starting # are ignored, so the list can carry notes.
 * A missing image is not an error - the tile just renders on the brand colour.
 */
function aqm_areas_rows() {

	$settings = aqm_areas_settings();
	$rows     = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $settings['list'] ) as $line ) {

		$line = trim( $line );
		if ( '' === $line || '#' === $line[0] ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( '' === $parts[0] ) {
			continue;
		}

		$rows[] = array(
			'name'  => $parts[0],
			'url'   => isset( $parts[1] ) ? $parts[1] : '',
			'image' => isset( $parts[2] ) ? $parts[2] : '',
		);
	}

	return $rows;
}

/* =====================================================================
 * FRONT END
 * ================================================================== */

function aqm_areas_shortcode( $atts ) {

	$settings = aqm_areas_settings();

	$a = shortcode_atts(
		array(
			'heading' => $settings['heading'],
			'eyebrow' => $settings['eyebrow'],
			'columns' => (int) $settings['columns'],
			'limit'   => 0,
			// 'tiles' (default) for a page section; 'list' for a plain column of
			// links, which is what a footer needs. Added 5 Sep 2026 when the
			// Elementor footer turned out to hold a THIRD hand-built copy of the
			// same 14 areas - and one that said "Niagara" where the other two
			// said "Niagara Falls". One list, three renderings.
			'layout'  => 'tiles',
		),
		$atts,
		'aqm_areas'
	);

	$rows = aqm_areas_rows();
	if ( (int) $a['limit'] > 0 ) {
		$rows = array_slice( $rows, 0, (int) $a['limit'] );
	}
	if ( ! $rows ) {
		return '';
	}

	$cols   = max( 1, min( 8, (int) $a['columns'] ) );
	$target = ! empty( $settings['new_tab'] ) ? ' target="_blank" rel="noopener"' : '';

	ob_start();
	aqm_areas_styles();

	/* ---------- list layout: a plain column of links, for footers ---------- */

	if ( 'list' === $a['layout'] ) {

		echo '<div class="aqm-areas aqm-areas-listwrap">';

		if ( '' !== trim( (string) $a['heading'] ) ) {
			echo '<h2 class="aqm-areas-heading is-list">' . esc_html( $a['heading'] ) . '</h2>';
		}

		echo '<ul class="aqm-areas-list">';
		foreach ( $rows as $row ) {
			if ( $row['url'] ) {
				printf(
					'<li><a href="%s"%s>%s</a></li>',
					esc_url( $row['url'] ),
					$target,
					esc_html( $row['name'] )
				);
			} else {
				printf( '<li>%s</li>', esc_html( $row['name'] ) );
			}
		}
		echo '</ul></div>';

		return ob_get_clean();
	}

	/* ---------- tile layout ---------- */

	echo '<div class="aqm-areas" style="--aqm-areas-cols:' . (int) $cols . '">';

	if ( '' !== trim( (string) $a['eyebrow'] ) ) {
		echo '<p class="aqm-areas-eyebrow">' . esc_html( $a['eyebrow'] ) . '</p>';
	}
	if ( '' !== trim( (string) $a['heading'] ) ) {
		echo '<h2 class="aqm-areas-heading">' . esc_html( $a['heading'] ) . '</h2>';
	}

	echo '<div class="aqm-areas-grid">';

	foreach ( $rows as $row ) {

		$style = $row['image']
			? ' style="background-image:url(' . esc_url( $row['image'] ) . ')"'
			: '';

		// A tile with no URL is still shown - it just is not a link. Better than
		// a link that goes nowhere, which is what the old markup did.
		if ( $row['url'] ) {
			printf(
				'<a class="aqm-areas-tile" href="%s"%s%s><span class="aqm-areas-name">%s</span></a>',
				esc_url( $row['url'] ),
				$target,
				$style, // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $row['name'] )
			);
		} else {
			printf(
				'<div class="aqm-areas-tile is-plain"%s><span class="aqm-areas-name">%s</span></div>',
				$style, // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $row['name'] )
			);
		}
	}

	echo '</div></div>';

	return ob_get_clean();
}
add_shortcode( 'aqm_areas', 'aqm_areas_shortcode' );

/** Styles, printed once per page. Inline: nothing to 404, nothing to combine. */
function aqm_areas_styles() {

	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	?>
	<style>
	/* Dropped into somebody else's theme, so link colour, underline and text
	   alignment are all pinned - themes style <a> hard. */
	.aqm-areas{font:16px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
	.aqm-areas *{box-sizing:border-box}
	.aqm-areas-eyebrow{margin:0 0 4px;font-size:14px;letter-spacing:.08em;
		text-transform:uppercase;color:#8a8f98}
	.aqm-areas-heading{margin:0 0 18px;font-size:32px;line-height:1.2}
	.aqm-areas-grid{display:grid;gap:14px;
		grid-template-columns:repeat(var(--aqm-areas-cols,7),minmax(0,1fr))}
	.aqm-areas-tile{position:relative;display:flex;align-items:flex-end;
		justify-content:center;min-height:150px;border-radius:10px;overflow:hidden;
		background-size:cover;background-position:center;text-decoration:none!important;
		box-shadow:0 4px 14px rgba(15,23,42,.12);transition:transform .15s,box-shadow .15s}
	.aqm-areas-tile:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(15,23,42,.2)}
	/* The scrim keeps white text readable over any photograph. Without it the
	   name vanishes on a bright sky, which is most of these images. */
	.aqm-areas-tile::after{content:"";position:absolute;inset:0;
		background:linear-gradient(to top,rgba(0,0,0,.65) 0%,rgba(0,0,0,.15) 55%,rgba(0,0,0,0) 100%)}
	.aqm-areas-name{position:relative;z-index:1;padding:12px 10px;color:#fff!important;
		font-weight:600;font-size:16px;text-align:center;text-shadow:0 1px 3px rgba(0,0,0,.5)}
	@media (max-width:1100px){.aqm-areas-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
	@media (max-width:700px){.aqm-areas-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
		.aqm-areas-heading{font-size:26px}}

	/* layout="list" - footers. Colour is INHERITED rather than set, so the same
	   markup works on a dark footer and a light page without a second setting. */
	.aqm-areas-listwrap{font-size:inherit;line-height:inherit;color:inherit}
	.aqm-areas-heading.is-list{font-size:1.15em;margin:0 0 10px}
	.aqm-areas-list{list-style:none;margin:0;padding:0}
	.aqm-areas-list li{margin:0 0 6px}
	.aqm-areas-list a{color:inherit;text-decoration:none}
	.aqm-areas-list a:hover,.aqm-areas-list a:focus{text-decoration:underline}
	</style>
	<?php
}

/* =====================================================================
 * ADMIN
 * ================================================================== */

add_action(
	'admin_menu',
	function () {
		add_menu_page(
			'AQM Areas',
			'AQM Areas',
			'manage_options',
			'aqm-areas',
			'aqm_areas_screen',
			'dashicons-location-alt',
			59
		);
	}
);

function aqm_areas_screen() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.' );
	}

	$settings = aqm_areas_settings();
	$rows     = aqm_areas_rows();

	echo '<div class="wrap"><h1>Areas We Serve</h1>';

	if ( isset( $_GET['msg'] ) && 'saved' === $_GET['msg'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>Saved. Every page using '
			. '<code>[aqm_areas]</code> is updated.</p></div>';
	}

	echo '<p>One list, used everywhere. Change it here and every page that carries '
		. '<code>[aqm_areas]</code> updates at once &mdash; there is no second copy to keep in step.</p>';

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'aqm_areas_save' );
	echo '<input type="hidden" name="action" value="aqm_areas_save">';

	echo '<table class="form-table"><tbody>';
	printf(
		'<tr><th>Heading</th><td><input name="heading" type="text" value="%s" class="regular-text">'
		. '<p class="description">Leave blank for no heading.</p></td></tr>',
		esc_attr( $settings['heading'] )
	);
	printf(
		'<tr><th>Eyebrow line</th><td><input name="eyebrow" type="text" value="%s" class="regular-text">'
		. '<p class="description">Small line above the heading. Usually blank.</p></td></tr>',
		esc_attr( $settings['eyebrow'] )
	);
	printf(
		'<tr><th>Columns</th><td><input name="columns" type="number" min="1" max="8" value="%d" class="small-text">'
		. '<p class="description">On a desktop. Drops to 4 on a tablet and 2 on a phone automatically.</p></td></tr>',
		(int) $settings['columns']
	);
	printf(
		'<tr><th>Open in a new tab</th><td><label><input name="new_tab" type="checkbox" value="1"%s> '
		. 'Yes &mdash; the searches are on rem.ax, so a new tab keeps your site open</label></td></tr>',
		checked( ! empty( $settings['new_tab'] ), true, false )
	);
	echo '</tbody></table>';

	echo '<h2>The list</h2>'
		. '<p>One area per line: <code>Name | Search URL | Image URL</code><br>'
		. 'Lines beginning <code>#</code> are notes and are ignored. Leave the image blank '
		. 'and the tile shows the name on its own.</p>';

	printf(
		'<textarea name="list" rows="18" style="width:100%%;font-family:monospace;font-size:13px">%s</textarea>',
		esc_textarea( $settings['list'] )
	);

	submit_button( 'Save areas' );
	echo '</form>';

	printf( '<h2>Currently %d area%s</h2>', count( $rows ), 1 === count( $rows ) ? '' : 's' );
	echo '<table class="widefat striped" style="max-width:900px"><thead><tr>'
		. '<th>Name</th><th>Links to</th><th>Image</th></tr></thead><tbody>';
	foreach ( $rows as $row ) {
		printf(
			'<tr><td><strong>%s</strong></td><td>%s</td><td>%s</td></tr>',
			esc_html( $row['name'] ),
			$row['url'] ? '<code>' . esc_html( $row['url'] ) . '</code>' : '<em style="color:#b32d2e">no link</em>',
			$row['image'] ? '<code>' . esc_html( wp_basename( $row['image'] ) ) . '</code>' : '<em>none</em>'
		);
	}
	echo '</tbody></table>';

	echo '<h2>Where to put it</h2>'
		. '<table class="widefat striped" style="max-width:820px"><tbody>'
		. '<tr><td><code>[aqm_areas]</code></td><td>The block, with the settings above.</td></tr>'
		. '<tr><td><code>[aqm_areas columns="4"]</code></td><td>Override the column count here only.</td></tr>'
		. '<tr><td><code>[aqm_areas heading=""]</code></td><td>No heading &mdash; when the page already has one.</td></tr>'
		. '<tr><td><code>[aqm_areas limit="7"]</code></td><td>Only the first seven areas.</td></tr>'
		. '</tbody></table>';

	echo '<h2>Preview</h2>';
	echo '<div style="max-width:1100px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:20px">';
	echo aqm_areas_shortcode( array() ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</div>';

	echo '</div>';
}

add_action(
	'admin_post_aqm_areas_save',
	function () {

		check_admin_referer( 'aqm_areas_save' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed.' );
		}

		update_option(
			'aqm_areas_settings',
			array(
				// sanitize_textarea_field keeps the line breaks; sanitize_text_field
				// would flatten the whole list into one line.
				'list'    => isset( $_POST['list'] ) ? sanitize_textarea_field( wp_unslash( $_POST['list'] ) ) : '',
				'heading' => isset( $_POST['heading'] ) ? sanitize_text_field( wp_unslash( $_POST['heading'] ) ) : '',
				'eyebrow' => isset( $_POST['eyebrow'] ) ? sanitize_text_field( wp_unslash( $_POST['eyebrow'] ) ) : '',
				'columns' => isset( $_POST['columns'] ) ? max( 1, min( 8, absint( wp_unslash( $_POST['columns'] ) ) ) ) : 7,
				'new_tab' => empty( $_POST['new_tab'] ) ? 0 : 1,
			),
			false
		);

		wp_safe_redirect( admin_url( 'admin.php?page=aqm-areas&msg=saved' ) );
		exit;
	}
);
