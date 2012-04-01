package com.baselogic.javaee6.domain;

import com.baselogic.javaee6.Constants;

import javax.persistence.Column;
import javax.persistence.Embeddable;
import javax.persistence.EnumType;
import javax.persistence.Enumerated;
import java.io.Serializable;

/**
 * [Title]
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 * @since 2011
 */
@Embeddable
public class Phone  implements Serializable {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//

    @Enumerated(EnumType.STRING)
    private PhoneType type;

    @Column
    private String areaCode;

    @Column(name = Constants.PHONE_NUMBER)
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
