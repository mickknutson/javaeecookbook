package com.baselogic.javaee6.domain;

import javax.xml.bind.annotation.XmlRootElement;
import java.io.Serializable;
import java.util.*;

/**
 * Customer Class
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
@XmlRootElement(name = "Customer")
public class Customer implements Serializable {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    private Long id;

    private String username;
    private String firstName;
    private String lastName;

    private Collection<String> hobbies = new HashSet<String>();

    private List<Phone> phones;

    private Map<String, Address> addresses = new HashMap<String, Address>();

    private Collection<Contact> contacts;

    private String description;

    //-----------------------------------------------------------------------//
    // Constructors
    //-----------------------------------------------------------------------//

    public Customer() {
    }

    public Customer(String username, String firstName, String lastName) {
        this.username = username;
        this.firstName = firstName;
        this.lastName = lastName;
    }

    //-----------------------------------------------------------------------//
    // Setters / Getters
    //-----------------------------------------------------------------------//
    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getFirstName() {
        return firstName;
    }

    public void setFirstName(String firstName) {
        this.firstName = firstName;
    }

    public String getLastName() {
        return lastName;
    }

    public void setLastName(String lastName) {
        this.lastName = lastName;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public List<Phone> getPhones() {
        return phones;
    }

    public void setPhones(List<Phone> phones) {
        this.phones = phones;
    }

    public Map<String, Address> getAddresses() {
        return addresses;
    }

    public void setAddresses(Map<String, Address> addresses) {
        this.addresses = addresses;
    }

    public Collection<String> getHobbies() {
        return hobbies;
    }

    public void setHobbies(Collection<String> hobbies) {
        this.hobbies = hobbies;
    }

    //-----------------------------------------------------------------------//
    // toString
    //-----------------------------------------------------------------------//

    @Override
    public String toString() {
        return new org.apache.commons.lang.builder.ToStringBuilder(this)
                .append("id", id)
                .append("username", username)
                .append("firstName", firstName)
                .append("lastName", lastName)
                .append("hobbies", hobbies)
                .append("phones", phones)
                .append("addresses", addresses)
                .append("contacts", contacts)
                .append("description", description)
                .toString();
    }

}
