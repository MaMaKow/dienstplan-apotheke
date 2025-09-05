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
package Selenium.Utilities;

/**
 *
 * @author Mandelkow
 */
import com.google.common.io.BaseEncoding;
import com.google.gson.Gson;
import com.google.gson.annotations.SerializedName;
import java.nio.charset.StandardCharsets;
import java.util.Base64;
import java.util.List;
import javax.mail.internet.MimeUtility;
//import org.apache.commons.codec.net.MimeUtility;

import javax.security.auth.Subject;

public class EmailParser {

    private Email email;

    public EmailParser(String mailJsonString) {
        Gson gson = GsonProvider.createGson();
        email = gson.fromJson(mailJsonString, Email.class);
        if (email != null && email.getContent() != null && email.getContent().getHeaders() != null) {
            String subject = email.getContent().getHeaders().getSubject();
            if (null == subject || subject.isEmpty()) {
                System.out.println("Subject is empty or not found.");
            }
        } else {
            System.out.println("Email structure is invalid.");
        }
    }

    public String getSubject() {
        Content content = email.getContent();
        //System.out.println(content);
        Headers headers = content.getHeaders();
        //System.out.println(headers);
        String subject = headers.getSubject();
        //System.out.println(subject);
        return subject;
    }

    // Classes matching JSON structure
    static class Email {

        @SerializedName("Content")
        private Content content;

        @SerializedName("Headers")
        private Headers headers;

        @SerializedName("Body")
        private String body;

        public Content getContent() {
            return content;
        }

        @Override
        public String toString() {
            return "Content{headers=" + headers + ", body=" + body + "}";
        }
    }

    static class Content {

        @SerializedName("Headers")
        private Headers headers;

        public Headers getHeaders() {
            return headers;
        }
    }

    static class Headers {

        @SerializedName("Subject")
        private List<String> subject;

        @SerializedName("From")
        private List<String> from;

        @SerializedName("To")
        private List<String> to;

        @SerializedName("Date")
        private List<String> date;

        public String getSubject() {
            //System.out.println("subject: " + subject.get(0));
            try {
                String decodedSubject = MimeUtility.decodeText(subject.get(0));
                //System.out.println("Decoded subject: " + decodedSubject);
                return decodedSubject;
            } catch (Exception e) {
                e.printStackTrace();
            } finally {
                return subject.get(0);
            }
        }

        public List<String> getFrom() {
            return from;
        }

        public List<String> getTo() {
            return to;
        }

        public List<String> getDate() {
            return date;
        }

        @Override
        public String toString() {
            return "Headers{subject=" + subject + ", from=" + from + ", to=" + to + ", date=" + date + "}";
        }
    }

}
