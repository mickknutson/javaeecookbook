package com.baselogic.test;

import org.dbunit.dataset.Column;
import org.dbunit.dataset.filter.IColumnFilter;

/**
 * Null Primary Key Filter Class
 * <p/>
 * <h2>Java EE6 Cookbook for Securing, Tuning and Extending Enterprise applications.</h2>
 * <p>Packt Publishing (http://www.packtpub.com)</p>
 *
 * @author Mick Knutson (<a href="http://www.baselogic.com">http://www.baselogic.com</a>)
 *         <a href="http://www.mickknutson.com">http://www.mickknutson.com</a>
 * @since 2011
 */
public class NullPrimaryKeyFilter implements IColumnFilter {
    private String[] keys = null;

    public NullPrimaryKeyFilter(String... keys) {
        this.keys = keys;
    }

    public boolean accept(String tableName, Column column) {
        for(String key: keys){
            if(column.getColumnName().equalsIgnoreCase(key)){
                return true;
            }
        }
        return false;
    }
}
