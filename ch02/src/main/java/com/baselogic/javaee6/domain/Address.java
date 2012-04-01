package com.baselogic.javaee6.domain;

import javax.persistence.*;
import java.io.Serializable;

/**
 * Address Class
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
@Embeddable
public class Address  implements Serializable {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    @Enumerated(EnumType.STRING)
    private AddressType type;

    @Column
    private String street;
    private String street2;
    private String city;
    private String state;
    private String province;
    private String postCode;

    //-----------------------------------------------------------------------//
    // Constructors
    //-----------------------------------------------------------------------//

    public Address() {
    }

    public Address(AddressType type,
                   String street,
                   String city,
                   String state,
                   String postCode) {
        this.type = type;
        this.street = street;
        this.street2 = street2;
        this.city = city;
        this.state = state;
        this.province = province;
        this.postCode = postCode;
    }

//-----------------------------------------------------------------------//
    // Setters / Getters
    //-----------------------------------------------------------------------//

    public String getStreet() {
        return street;
    }

    public void setStreet(String street) {
        this.street = street;
    }

    public String getStreet2() {
        return street2;
    }

    public void setStreet2(String street2) {
        this.street2 = street2;
    }

    public String getCity() {
        return city;
    }

    public void setCity(String city) {
        this.city = city;
    }

    public String getState() {
        return state;
    }

    public void setState(String state) {
        this.state = state;
    }

    public String getProvince() {
        return province;
    }

    public void setProvince(String province) {
        this.province = province;
    }

    public String getPostCode() {
        return postCode;
    }

    public void setPostCode(String postCode) {
        this.postCode = postCode;
    }

    public AddressType getType() {
        return type;
    }

    public void setType(AddressType type) {
        this.type = type;
    }
}

