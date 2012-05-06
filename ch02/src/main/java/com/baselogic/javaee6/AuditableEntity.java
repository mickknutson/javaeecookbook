package com.baselogic.javaee6;

import org.apache.commons.lang.builder.ToStringBuilder;

import javax.persistence.*;
import java.io.Serializable;
import java.util.Calendar;

/**
 * BASE Entity Class
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
