package com.baselogic.javaee6.domain;

import java.io.Serializable;

/**
 * [Title]
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 * @since 2011
 */
public class Phone implements Serializable {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    private PhoneType type;

    private String areaCode;

    private String number;

    //-----------------------------------------------------------------------//
    // Constructors
    //-----------------------------------------------------------------------//

    public Phone() {
    }

    public Phone(PhoneType type, String areaCode, String number) {
        this.type = type;
        this.areaCode = areaCode;
        this.number = number;
    }

    //-----------------------------------------------------------------------//
    // Setters / Getters
    //-----------------------------------------------------------------------//

    public PhoneType getType() {
        return type;
    }

    public void setType(PhoneType type) {
        this.type = type;
    }

    public String getAreaCode() {
        return areaCode;
    }

    public void setAreaCode(String areaCode) {
        this.areaCode = areaCode;
    }

    public String getNumber() {
        return number;
    }

    public void setNumber(String number) {
        this.number = number;
    }
}
