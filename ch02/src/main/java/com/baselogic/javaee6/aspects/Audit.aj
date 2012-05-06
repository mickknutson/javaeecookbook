package com.baselogic.javaee6.aspects;

import javax.persistence.PrePersist;
import javax.persistence.PreUpdate;

/**
 * Audit Aspect
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
