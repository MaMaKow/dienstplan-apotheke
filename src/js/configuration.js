/*
 * Copyright (C) 2025 Mandelkow
 *
 * Dienstplan Apotheke
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */


document.addEventListener("DOMContentLoaded", function () {
    const stateSelect = document.querySelector('select[name="stateCode"]');
    const countrySelect = document.querySelector('select[name="countryCode"]');
    const statesByCountry = {
        "DE": {
            "DE-BW": "Baden-Württemberg",
            "DE-BY": "Bayern",
            "DE-BE": "Berlin",
            "DE-BB": "Brandenburg",
            "DE-HB": "Bremen",
            "DE-HH": "Hamburg",
            "DE-HE": "Hessen",
            "DE-MV": "Mecklenburg-Vorpommern",
            "DE-NI": "Niedersachsen",
            "DE-NW": "Nordrhein-Westfalen",
            "DE-RP": "Rheinland-Pfalz",
            "DE-SL": "Saarland",
            "DE-SN": "Sachsen",
            "DE-ST": "Sachsen-Anhalt",
            "DE-SH": "Schleswig-Holstein",
            "DE-TH": "Thüringen"
        },
        "GB": {
            "GB-ENG": "England",
            "GB-NIR": "Northern Ireland",
            "GB-SCT": "Scotland",
            "GB-WLS": "Wales"
        },
        "FR": {
            "FR-ARA": "Auvergne-Rhône-Alpes",
            "FR-BFC": "Bourgogne-Franche-Comté",
            "FR-BRE": "Bretagne",
            "FR-CVL": "Centre-Val de Loire",
            "FR-COR": "Corse",
            "FR-GES": "Grand Est",
            "FR-HDF": "Hauts-de-France",
            "FR-IDF": "Île-de-France",
            "FR-NOR": "Normandie",
            "FR-NAQ": "Nouvelle-Aquitaine",
            "FR-OCC": "Occitanie",
            "FR-PDL": "Pays de la Loire",
            "FR-PAC": "Provence-Alpes-Côte d'Azur",
            "FR-A": "Alsace", // zusätzich zum offiziellen ISO 3166-2:FR
            "FR-57": "Moselle", // zusätzich zum offiziellen ISO 3166-2:FR

        }
    };
    function updateStates() {
        const selectedCountry = countrySelect.value;
        const states = statesByCountry[selectedCountry] || {};
        stateSelect.innerHTML = ""; // Vorherige Optionen löschen
        Object.entries(states).forEach(([code, name]) => {
            const option = document.createElement("option");
            option.value = code;
            option.textContent = name;
            stateSelect.appendChild(option);
        });

        if (stateSelect.options.length === 0) {
            stateSelect.disabled = true;
        } else {
            stateSelect.disabled = false;
        }
    }
    countrySelect.addEventListener("change", updateStates);
});
