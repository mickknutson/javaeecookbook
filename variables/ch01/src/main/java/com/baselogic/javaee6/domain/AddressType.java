package com.baselogic.javaee6.domain;

import java.io.Serializable;

/**
 * Address Type
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
public enum AddressType implements Serializable {
    BUSINESS,
    RESIDENTIAL,
    SECOND_HOME
}
