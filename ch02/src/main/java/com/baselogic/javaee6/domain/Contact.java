package com.baselogic.javaee6.domain;

import com.baselogic.javaee6.Constants;

import javax.persistence.*;

/**
 * Contact Class
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
