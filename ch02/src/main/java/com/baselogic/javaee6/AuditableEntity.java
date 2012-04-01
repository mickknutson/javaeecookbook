package com.baselogic.javaee6;

import org.apache.commons.lang.builder.ToStringBuilder;

import javax.persistence.*;
import java.io.Serializable;
import java.util.Calendar;

/**
 * BASE Entity Class
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
@MappedSuperclass
@EntityListeners({AuditListener.class})
public abstract class AuditableEntity implements Serializable {
    public static ThreadLocal currentUser = new ThreadLocal();

    @Column(name = Constants.AUDIT_USER)
    protected String auditUser;

    @Temporal(TemporalType.TIMESTAMP)
    @Column(name = Constants.AUDIT_TIMESTAMP)
    protected Calendar auditTimestamp;

    public String getAuditUser() {
        return auditUser;
    }

    public void setAuditUser(String auditUser) {
        this.auditUser = auditUser;
    }

    public Calendar getAuditTimestamp() {
        return auditTimestamp;
    }

    public void setAuditTimestamp(Calendar auditTimestamp) {
        this.auditTimestamp = auditTimestamp;
    }

    @PrePersist
    @PreUpdate
    public void updateAuditInfo() {
        setAuditUser((String) currentUser.get());
        setAuditTimestamp(Calendar.getInstance());
    }

    //-----------------------------------------------------------------------//
    // toString
    //-----------------------------------------------------------------------//

    @Override
    public String toString() {
        return ToStringBuilder.reflectionToString(this);
    }
}
