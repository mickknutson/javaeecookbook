package com.baselogic.javaee6.domain;

import com.baselogic.javaee6.Constants;

import javax.persistence.*;

/**
 * Contact Class
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
@Entity
@Table
public class Contact {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    @Id
    @GeneratedValue
    private Long id;

    @ManyToOne
    @JoinColumn(name = Constants.CONTACT_ENTRY)
    private Customer customerEntry;

    //-----------------------------------------------------------------------//
    // Constructors
    //-----------------------------------------------------------------------//

    //-----------------------------------------------------------------------//
    // Setters / Getters
    //-----------------------------------------------------------------------//



}
