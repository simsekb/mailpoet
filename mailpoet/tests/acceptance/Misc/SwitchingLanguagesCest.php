<?php

namespace MailPoet\Test\Acceptance;

class SwitchingLanguagesCest {
  public function switchLanguagesAndVerify(\AcceptanceTester $i) {
    $i->wantTo('Switch WP language and verify it in MailPoet plugin');
    
    // Install German, French (Canada) & Greek languages
    $i->cli(['language', 'core', 'install', 'de_DE']);
    $i->cli(['language', 'core', 'install', 'fr_CA']);
    $i->cli(['language', 'core', 'install', 'el']);

    $i->login();

    // Switch to German language
    $i->cli(['site', 'switch-language', 'de_DE']);
    $i->amOnPage('/wp-admin/update-core.php');
    $i->waitForText('WordPress-Aktualisierungen');
    $i->click('Übersetzungen aktualisieren');
    $i->waitForText('Weiter zur WordPress-Aktualisierungs-Seite');

    // Verify German language in MailPoet
    $i->amOnPage('/wp-admin/admin.php?page=mailpoet-newsletters#/new');
    $i->waitForElement('[data-automation-id="create_standard"]');
    $i->see('Sende einen Newsletter mit Bildern, Buttons, Trennlinien, und Sozialen Lesezeichen. Oder sende eine einfache Text-E-Mail.');
    $i->click('Formulare');
    $i->waitForElement('[data-automation-id="filters_alle"]');
    $i->see('Alle', '[data-automation-id="filters_alle"]');
    $i->click('Abonnenten');
    $i->waitForElement('[data-automation-id="listing-column-header-email"]');
    $i->see('Abonnent', '[data-automation-id="listing-column-header-email"]');
    $i->click('Listen');
    $i->waitForElement('[data-automation-id="listing-column-header-description"]');
    $i->see('Beschreibung', '[data-automation-id="listing-column-header-description"]');
    $i->click('Einstellungen');
    $i->waitForElement('[data-automation-id="signup_settings_tab"]');
    $i->see('Registrierungsbestätigung', '[data-automation-id="signup_settings_tab"]');

    // Switch to French (Canada) language
    $i->cli(['site', 'switch-language', 'fr_CA']);
    $i->amOnPage('/wp-admin/update-core.php');
    $i->waitForText('Mises à jour de WordPress');
    $i->click('Mise à jour des traductions');
    $i->waitForText('Mise à jour des traductions');

    // Verify French (Canada) language in MailPoet
    $i->amOnPage('/wp-admin/admin.php?page=mailpoet-newsletters#/new');
    $i->waitForElement('[data-automation-id="create_standard"]');
    $i->see('Envoyez une infolettre avec des images, des boutons, des séparateurs et des liens vers vos réseaux sociaux. Ou envoyez un courriel basique en texte.');
    $i->click('Formulaires');
    $i->waitForElement('[data-automation-id="filters_tous"]');
    $i->see('Tous', '[data-automation-id="filters_tous"]');
    $i->click('Abonnés');
    $i->waitForElement('[data-automation-id="filters_abonné"]');
    $i->see('Abonné', '[data-automation-id="filters_abonné"]');
    $i->click('Listes');
    $i->waitForElement('[data-automation-id="listing-column-header-average_subscriber_score"]');
    $i->see('Score de liste', '[data-automation-id="listing-column-header-average_subscriber_score"]');
    $i->click('Réglages');
    $i->waitForElement('[data-automation-id="basic_settings_tab"]');
    $i->see('Essentiels', '[data-automation-id="basic_settings_tab"]');

    // Switch to Greek language
    $i->cli(['site', 'switch-language', 'el']);
    $i->amOnPage('/wp-admin/update-core.php');
    $i->waitForText('Ενημερώσεις WordPress');
    $i->click('Ενημέρωση μεταφράσεων');
    $i->waitForText('Ενημέρωση μεταφράσεων');

    // Verify Greek language in MailPoet
    $i->amOnPage('/wp-admin/admin.php?page=mailpoet-newsletters#/new');
    $i->waitForElement('[data-automation-id="create_standard"]');
    $i->see('Στείλτε ένα ενημερωτικό δελτίο με εικόνες, κουμπιά, διαχωριστικά και κοινωνικούς σελιδοδείκτες. Ή απλά στείλτε ένα βασικό μήνυμα ηλεκτρονικού ταχυδρομείου.');
    $i->click('Φόρμες');
    $i->waitForElement('[data-automation-id="filters_όλα"]');
    $i->see('Όλα', '[data-automation-id="filters_όλα"]');
    $i->click('Συνδρομητές');
    $i->waitForElement('[data-automation-id="listing-column-header-status"]');
    $i->see('Κατάσταση', '[data-automation-id="listing-column-header-status"]');
    $i->click('Λίστες');
    $i->waitForElement('[data-automation-id="listing-column-header-average_subscriber_score"]');
    $i->see('Βαθμολογία λίστας', '[data-automation-id="listing-column-header-average_subscriber_score"]');
    $i->click('Ρυθμίσεις');
    $i->waitForElement('[data-automation-id="signup_settings_tab"]');
    $i->see('Επιβεβαίωση Εγγραφής', '[data-automation-id="signup_settings_tab"]');
  }
}
