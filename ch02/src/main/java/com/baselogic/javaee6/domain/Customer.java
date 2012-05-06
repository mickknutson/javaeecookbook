package com.baselogic.javaee6.domain;

import com.baselogic.javaee6.AuditableEntity;
import com.baselogic.javaee6.Constants;

import javax.persistence.*;
import javax.xml.bind.annotation.XmlRootElement;
import java.util.*;

/**
 * Customer Class
 *
 * @author Mick Knutson
 * @see <a href="http://www.baselogic.com">Blog: http://baselogic.com</a>
 * @see <a href="http://linkedin.com/in/mickknutson">LinkedIN: http://linkedin.com/in/mickknutson</a>
 * @see <a href="http://twitter.com/mickknutson">Twitter: http://twitter.com/mickknutson</a>
 * @see <a href="http://github.com/mickknutson">Git hub: http://github.com/mickknutson</a>
 *
 * @see <a href="http://www.packtpub.com/java-ee6-securing-tuning-extending-enterprise-applications-cookbook/book">JavaEE 6 Cookbook Packt</a>
 * @see <a href="http://www.amazon.com/Cookbook-securing-extending-enterprise-applications/dp/1849683166">JavaEE 6 Cookbook Amazon</a>
 *
 * @since 2012
 */
@XmlRootElement(name = "Customer")
@Entity
@Table
@NamedQuery(name = Constants.FINDALLFINDERNAME,
            query = Constants.FINDALLQUERY)
public class Customer extends AuditableEntity {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    @Id
    @GeneratedValue
    private Long id;

    @Column(nullable = false)
    private String username;
    private String firstName;
    private String lastName;
    private Double discount;

    @ElementCollection
    @CollectionTable(name = Constants.HOBBIES, joinColumns = @JoinColumn(name = Constants.CUSTOMER_ID))
    @Column(name = Constants.HOBBY_NAME, nullable = true)
    private Collection<String> hobbies = new HashSet<String>();

    @ElementCollection
    @CollectionTable(name = Constants.PHONES, joinColumns = @JoinColumn(name = Constants.CUSTOMER_ID))
    @Column(name = Constants.CUSTOMER_PHONES, nullable = true)
    private List<Phone> phones;

    @ElementCollection
    @CollectionTable(name = Constants.CUSTOMER_ADDRESSES,
            joinColumns = @JoinColumn(name = Constants.CUSTOMER_ID))
    @MapKeyColumn(name = Constants.ADDRESS_KEY)
    private Map<String, Address> addresses = new HashMap<String, Address>();

    @OneToMany(cascade = {CascadeType.ALL},
            fetch = FetchType.EAGER,
            mappedBy = Constants.CONTACT_ENTRY)
    private Collection<Contact> contacts;

    @Column(length = 2000, nullable = true)
    private String description;

    //-----------------------------------------------------------------------//
    // Constructors
    //-----------------------------------------------------------------------//

    public Customer() {
        this.discount = 0.0;
    }

    public Customer(String username, String firstName, String lastName) {
        this.username = username;
        this.firstName = firstName;
        this.lastName = lastName;
        this.discount = 0.0;
    }

    //-----------------------------------------------------------------------//
    // Setters / Getters
    //-----------------------------------------------------------------------//
    public Long getId() {
        return id;
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

    public Double getDiscount() {
        return discount;
    }

    public void setDiscount(Double discount) {
        this.discount = discount;
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
}
