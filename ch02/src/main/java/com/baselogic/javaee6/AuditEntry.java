package com.baselogic.javaee6;

import java.io.Serializable;
import java.util.*;

import javax.persistence.*;

/**
 * Audit Entry Class
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
@Entity
public class AuditEntry implements Serializable {

    private static final long serialVersionUID = 9876543210L;

    @Id
    @GeneratedValue
    private Long id;

    @Column(name = Constants.AUDIT_USER)
    private String auditUser;

    @Column
    private Long eventId;
    private String tableName;

    @Enumerated(EnumType.STRING)
    private AuditOperation operation;

    @Temporal(TemporalType.TIMESTAMP)
    @Column(name = Constants.AUDIT_TIMESTAMP)
    private Calendar operationTime;

    @OneToMany(cascade = {CascadeType.ALL},
            fetch = FetchType.EAGER,
            mappedBy = Constants.AUDIT_ENTRY)
    private Collection<AuditField> fields;

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getAuditUser() {
        return auditUser;
    }

    public void setAuditUser(String auditUser) {
        this.auditUser = auditUser;
    }

    public Long getEventId() {
        return eventId;
    }

    public void setEventId(Long eventId) {
        this.eventId = eventId;
    }

    public String getTableName() {
        return tableName;
    }

    public void setTableName(String tableName) {
        this.tableName = tableName;
    }

    public AuditOperation getOperation() {
        return operation;
    }

    public void setOperation(AuditOperation operation) {
        this.operation = operation;
    }

    public Calendar getOperationTime() {
        return operationTime;
    }

    public void setOperationTime(Calendar operationTime) {
        this.operationTime = operationTime;
    }

    public Collection<AuditField> getFields() {
        return fields;
    }

    public void setFields(Collection<AuditField> fields) {
        this.fields = fields;
    }

    /**
     * Set hashCode to audit entry's ID
     */
    @Override
    public int hashCode() {
        return id.intValue();
    }

    /**
     * Assign equivalence based on audit entry's ID
     */
    @Override
    public boolean equals(Object obj) {
        if (obj instanceof AuditEntry) {
            if (((AuditEntry) obj).getId().equals(id))
                return true;
        }
        return false;
    }
}
