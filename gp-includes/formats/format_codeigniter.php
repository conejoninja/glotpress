<?php

class GP_Format_Codeigniter {

    var $name = 'CodeIgniter (.php)';
    var $extension = 'php';

    var $exported = '';

    function line( $string, $prepend_tabs = 0 ) {
        $this->exported .= str_repeat( "\t", $prepend_tabs ) . "$string\n";
    }

    function print_exported_file( $project, $locale, $translation_set, $entries ) {
        $this->line("<?php");
        foreach ( $entries as $entry ) {
            $translation = str_replace( "\n", "\\n", empty( $entry->translations ) ? $entry->context : $entry->translations[0] );
            $translation = $this->escape($translation);
            $this->line("\$lang[\"{$entry->context}\"] = \"$translation\";");
        }

        return $this->exported;
    }

    function read_translations_from_file( $file_name, $project = null ) {
        if ( is_null( $project ) ) return false;
        $translations = $this->read_originals_from_file( $file_name );
        if ( !$translations ) return false;
        $originals = GP::$original->by_project_id( $project->id );
        $new_translations = new Translations;
        foreach( $translations->entries as $key => $entry ) {
            // we have been using read_originals_from_file to parse the file
            // so we need to swap singular and translation
            $entry->translations = array( $entry->singular );
            $entry->singular = null;
            foreach( $originals as $original ) {
                if ( $original->context == $entry->context ) {
                    $entry->singular = $original->singular;
                    break;
                }
            }
            if ( !$entry->singular ) {
                error_log( sprintf( __("Missing context %s in project #%d"), $entry->context, $project->id ) );
                continue;
            }

            $new_translations->add_entry( $entry );
        }
        return $new_translations;
    }

    function read_originals_from_file( $file_name ) {
        include_once($file_name);

        if ( !is_array( $lang ) ) return false;
        $entries = new Translations;
        foreach( $lang as $k => $v ) {
            $entry = new Translation_Entry();
            $entry->context = $k;
            $entry->singular = $this->unescape( $v );
            $entry->translations = array();
            $entries->add_entry( $entry );
        }
        return $entries;
    }

    function unescape( $string ) {
        return stripslashes( $string );
    }

    function escape( $string ) {
        $string = addslashes( $string );
        return $string;
    }
}

GP::$formats['codeigniter'] = new GP_Format_Codeigniter;