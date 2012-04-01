package com.baselogic.javaee6.aspects;

import javax.persistence.PrePersist;
import javax.persistence.PreUpdate;

/**
 * Audit Aspect
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
public aspect Audit {
    declare parents: @Entity * implements AuditedEntity;

    public abstract class AuditedEntity {}

    @PrePersist
    public void Audit.AuditedEntity.prePersistAuditing() {
       //... auditing logic
    }

    @PreUpdate
    public void Audit.AuditedEntity.preUpdateAuditing() {
       //... auditing logic
    }

    //... similar code for @PreUpdate
}
