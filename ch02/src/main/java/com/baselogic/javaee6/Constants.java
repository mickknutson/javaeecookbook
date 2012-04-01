package com.baselogic.javaee6;

/**
 * [Title]
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 * @since 2011
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
