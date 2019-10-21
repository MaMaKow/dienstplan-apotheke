<?php

/*
 * Copyright (C) 2019 Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Test if a password is secure.
 *
 * @see https://haveibeenpwned.com/API/v2#PwnedPasswords
 * @author Martin Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class have_i_been_pwned {

    //private $hash_list; //will not be stored
    private $hash_count;

    /**
     *
     * Check if a password has not been pwned
     *
     * @param type $password_string
     * @return boolean TRUE if the password is save to use. FALSE if it has been pwned in the past.
     * @throws Exception if the connection to api.pwnedpasswords.com failed.
     */
    public function password_is_secure($password_string) {
        $password_hash = strtoupper(sha1($password_string));
        try {
            $this->hash_count = $this->search_hash_in_list_from_haveibeenpwned($password_hash);
        } catch (Exception $exception) {
            throw $exception;
        }

        if (FALSE === $this->hash_count) {
            /*
             * The hash is not found. The password is secure:
             */
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Search a password hash in the haveibeenpwned database.
     *
     * <p>
     * The database is securely and privately accessed.
     * Only the first 5 chars of the hash are sent to the server.
     * It will send back a list of possible matches (around 500).
     * It is our task to look for our hash in that list.
     * If the hash is in the list, then the password is not safe.
     * </p>
     *
     * @param string $password_hash <p>preferably with uppercase letters. The API will return it's result in uppercase.</p>
     * @return boolean|int If the hash is not found, then the function returns FALSE. If the hash is pwned, then the function returns the count of times pwned.
     * @throws Exception if the connection to api.pwnedpasswords.com failed.
     */
    private function search_hash_in_list_from_haveibeenpwned($password_hash) {
        //$hash_list = array();
        $first_five_chars = substr($password_hash, 0, 5);
        $line_array = file('https://api.pwnedpasswords.com/range/' . $first_five_chars);
        if (FALSE === $line_array) {
            /*
             * The API could not be read. Does it still exist?
             */
            throw new Exception('Error while trying to reach api.pwnedpasswords.com');
        }
        foreach ($line_array as $line) {
            list($residual_35_chars, $count) = explode(':', $line);
            $pwnd_hash = $first_five_chars . $residual_35_chars;
            if ($pwnd_hash === $password_hash) {
                return (int) $count;
            }
            //$this->hash_list[$pwnd_hash] = (int) $count;
        }
        return FALSE;
    }

    /**
     * If the checked password has been pwned, then an explaining string can be generated.
     *
     * @return boolean|string
     */
    public function get_user_information_string() {
        if (0 >= $this->hash_count) {
            return FALSE;
        }
        $string = sprintf(ngettext('This password has been seen %1$s time before.', 'This password has been seen %1$s times before.', $this->hash_count), $this->hash_count)
                . " "
                . gettext("This password has previously appeared in a data breach and should never be used. If you've ever used it anywhere before, change it!");
        return $string;
    }

}
