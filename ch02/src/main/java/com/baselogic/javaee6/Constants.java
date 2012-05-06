package com.baselogic.javaee6;

/**
 * Constants
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
public interface Constants {

    //-----------------------------------------------------------------------//
    // Attributes
    //-----------------------------------------------------------------------//
    public static final String PERSISTENCEUNIT = "HSQL_PU";
    public static final String PERSISTENCEUNIT_DERBY = "DERBY_PU";
    public static final String FINDALLFINDERNAME = "findAll";
    public static final String FINDALLQUERY = "SELECT c FROM Customer c";

    //-----------------------------------------------------------------------//
    // Table / Column Names
    //-----------------------------------------------------------------------//
    public static final String AUDIT_USER = "AUDIT_USER";
    public static final String AUDIT_TIMESTAMP = "AUDIT_TIMESTAMP";

    public static final String PHONES = "PHONES";
    public static final String CUSTOMER_ID = "CUST_ID";
    public static final String CUSTOMER_PHONES = "CUST_PHONES";

    public static final String CUSTOMER_ADDRESSES = "CUST_ADDRESSES";
    public static final String ADDRESS_KEY = "ADDRESS_KEY";
    public static final String HOBBIES = "HOBBIES";
    public static final String HOBBY_NAME = "HOBBY_NAME";

    public static final String PHONE_NUMBER = "PHONE_NUMBER";
    public static final String AUDIT_ENTRY = "auditEntry";
    public static final String AUDIT_ENTRY_ID = "AUDIT_ENTRY_ID";
    public static final String AUDIT_FIELDS = "AUDIT_FIELDS";

    public static final String CONTACT_ENTRY = "customerEntry";

}
