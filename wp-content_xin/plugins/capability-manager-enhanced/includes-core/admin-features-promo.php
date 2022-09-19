<?php
/**
 * Capability Manager Admin Features Promo.
 *
 *    Copyright 2020, PublishPress <help@publishpress.com>
 *
 *    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    version 2 as published by the Free Software Foundation.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

?>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur hidecsselement">
   <td class="features-section-header" colspan="2">
      <strong><i class="dashicons dashicons-hidden"></i> Hide Css Element</strong>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur hidecsselement">
   <td class="restrict-column ppc-menu-checkbox">
      <input type="checkbox">
   </td>
   <td class="menu-column ppc-menu-item">
      <label>
         <span class="menu-item-link">
         <strong> — Welcome panel <small
            class="entry">(#welcome-panel)</small> &nbsp; <span
            class="ppc-custom-features-css-delete red-pointer"
            data-id="16188f4b317b0b"><small>(Delete)</small></span></strong></span>
      </label>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur hidecsselement">
   <td class="restrict-column ppc-menu-checkbox">
   <input type="checkbox">
   </td>
   <td class="menu-column ppc-menu-item">
      <label>
         <span class="menu-item-link restricted">
         <strong>
         — Woocomerce and site health <small
            class="entry">(#wc_admin_dashboard_setup, #dashboard_site_health)</small> &nbsp; <span
            class="ppc-custom-features-css-delete red-pointer"
            data-id="16188f4dfe6150"><small>(Delete)</small></span></strong></span>
      </label>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur hidecsselement">
   <td class="restrict-column ppc-menu-checkbox">
   <input type="checkbox">
   </td>
   <td class="menu-column ppc-menu-item">
      <label>
         <span class="menu-item-link">
         <strong>
         — Others <small
            class="entry">(#dashboard_activity, #dashboard_right_now,#dashboard_primary)</small> &nbsp; <span
            class="ppc-custom-features-css-delete red-pointer"
            data-id="16188f50c5b7b7"><small>(Delete)</small></span> </strong></span>
      </label>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row">
   <td colspan="2">
      <div class="pp-promo-upgrade-notice">
         <p>
            <?php esc_html_e('You can block pages by URL or hide Admin elements by entering a CSS class or ID. This feature is available in PublishPress Capabilities Pro.',
               'capsman-enhanced'); ?>
         </p>
         <p>
            <a href="https://publishpress.com/links/capabilities-banner" target="_blank">
            <?php esc_html_e('Upgrade to Pro', 'capsman-enhanced'); ?>
            </a>
         </p>
      </div>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur blockedbyurl">
   <td class="features-section-header" colspan="2">
      <strong><i class="dashicons dashicons-admin-links"></i> Blocked by URL </strong>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur blockedbyurl">
   <td class="restrict-column ppc-menu-checkbox">
   <input type="checkbox">
   </td>
   <td class="menu-column ppc-menu-item">
      <label>
         <span class="menu-item-link">
         <strong>
         — Plugin add <small
            class="entry">(/wp-admin/plugin-install.php)</small> &nbsp; <span
            class="ppc-custom-features-url-delete red-pointer"
            data-id="16183aea9b16aa"><small>(Delete)</small></span> </strong></span>
      </label>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur blockedbyurl">
   <td class="restrict-column ppc-menu-checkbox">
   <input type="checkbox">
   </td>
   <td class="menu-column ppc-menu-item">
      <label>
         <span class="menu-item-link restricted">
         <strong>
         — Some settings pages <small
            class="entry">(/wp-admin/options-general.php, /wp-admin/options-writing.php, /wp-admin/options-reading.php)</small> &nbsp; <span
            class="ppc-custom-features-url-delete red-pointer"
            data-id="16183aedcbab7b"><small>(Delete)</small></span> </strong></span>
      </label>
   </td>
</tr>
<tr class="ppc-menu-row parent-menu pp-promo-overlay-row pp-promo-blur blockedbyurl">
   <td class="restrict-column ppc-menu-checkbox">
   <input type="checkbox">
   </td>
   <td class="menu-column ppc-menu-item">
      <label>
         <span class="menu-item-link">
         <strong>
         — TaxoPress Taxonomy Add <small
            class="entry">(/wp-admin/admin.php?page=st_taxonomies&amp;add=taxonomy)</small> &nbsp; <span
            class="ppc-custom-features-url-delete red-pointer"
            data-id="16183aefacc9bc"><small>(Delete)</small></span></strong></span>
      </label>
   </td>
</tr>

<?php
