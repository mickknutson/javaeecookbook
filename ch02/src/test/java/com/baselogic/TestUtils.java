package com.baselogic;

import com.baselogic.test.NullPrimaryKeyFilter;
import org.dbunit.database.DatabaseConfig;
import org.dbunit.database.DatabaseConnection;
import org.dbunit.database.IDatabaseConnection;
import org.dbunit.dataset.IDataSet;
import org.dbunit.dataset.xml.FlatXmlDataSet;
import org.dbunit.dataset.xml.FlatXmlDataSetBuilder;
import org.dbunit.ext.h2.H2DataTypeFactory;
import org.dbunit.operation.DatabaseOperation;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.persistence.EntityTransaction;
import javax.transaction.Transaction;
import javax.persistence.EntityManager;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.sql.Connection;

/**
 * Test Utils
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
public class TestUtils {

    private static final Logger logger = LoggerFactory.getLogger(TestUtils.class);

    //-----------------------------------------------------------------------//
    // DBUnit Helper Methods
    //-----------------------------------------------------------------------//

    /**
     * Seed data using DBUnit
     *
     * @param em                    EntityManager
     * @param dataSetFile           DBUnit dataSet file to import
     * @param nullPrimaryKeyFilters primary key's to use for DBUnit Import Filter
     * @throws Exception
     */
    public static void seedData(EntityManager em,
                                String dataSetFile,
                                String... nullPrimaryKeyFilters)
            throws Exception {

        EntityTransaction tx = em.getTransaction();
        tx.begin();

        Connection connection = em.unwrap(java.sql.Connection.class);

        logger.warn(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");

        try {
            IDatabaseConnection dbUnitCon = new DatabaseConnection(connection);

            dbUnitCon.getConfig().setProperty(DatabaseConfig.PROPERTY_DATATYPE_FACTORY,
                    new H2DataTypeFactory());

            if (nullPrimaryKeyFilters != null && nullPrimaryKeyFilters.length > 0) {
                // Set the property by passing the new IColumnFilter
                dbUnitCon.getConfig().setProperty(
                        DatabaseConfig.PROPERTY_PRIMARY_KEY_FILTER,
                        new NullPrimaryKeyFilter(nullPrimaryKeyFilters));
            }

            IDataSet dataSet = getDataSet(dataSetFile);

            DatabaseOperation.CLEAN_INSERT.execute(dbUnitCon, dataSet);
        } catch (Exception exc) {
            exc.printStackTrace();
        } finally {
            tx.commit();
            connection.close();
        }
    }

    protected static IDataSet getDataSet(String dataSetFile) throws Exception {
        return new FlatXmlDataSetBuilder().build(new FileInputStream(dataSetFile));
    }

    /**
     * Dump data using DBUnit
     *
     * @param em                    EntityManager
     * @param dataSetOutputFile     DBUnit dataSet output file for export.
     * @param nullPrimaryKeyFilters primary key's to use for DBUnit Import Filter
     * @throws Exception
     */
    public static void dumpData(EntityManager em,
                                String dataSetOutputFile,
                                String... nullPrimaryKeyFilters)
            throws Exception {

        EntityTransaction tx = em.getTransaction();
        tx.begin();

        Connection connection = em.unwrap(java.sql.Connection.class);

        try {
            IDatabaseConnection dbUnitCon = new DatabaseConnection(connection);
            dbUnitCon.getConfig().setProperty(DatabaseConfig.PROPERTY_DATATYPE_FACTORY,
                    new H2DataTypeFactory());

            if (nullPrimaryKeyFilters != null && nullPrimaryKeyFilters.length > 0) {
                // Set the property by passing the new IColumnFilter
                dbUnitCon.getConfig().setProperty(
                        DatabaseConfig.PROPERTY_PRIMARY_KEY_FILTER,
                        new NullPrimaryKeyFilter(nullPrimaryKeyFilters));
            }

            IDataSet dataSet = dbUnitCon.createDataSet();

            // If we use this method, the stream is closed in the 'finalizer'
            // and throws warnings which we do not want.
            //FlatXmlDataSet.write(dataSet, new FileOutputStream(dataSetOutputFile));

            // Explicitly closes the Stream (no warnings raised):
            FileOutputStream fos = new FileOutputStream(dataSetOutputFile);
            FlatXmlDataSet.write(dataSet, fos);
            fos.close();
        } catch (Exception exc) {
            exc.printStackTrace();
        } finally {
            tx.commit();
            connection.close();
        }
    }
}
