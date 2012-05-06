package com.baselogic.test;

import com.baselogic.javaee6.domain.*;

import java.util.*;

/**
 * Customer Test Fixture
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
public class CustomerFixture {

    static Random random = new Random();

    public static Customer createSingleCustomer() {
        String token = Long.toString(Math.abs(random.nextLong()*5), 36);

        Customer customer = new Customer();
        customer.setUsername(token + Math.random());
        customer.setFirstName(token + Math.random());
        customer.setLastName(token + Math.random());
        customer.setDescription(token + Math.random());
        customer.setHobbies(
                new HashSet<String>() {{
                    add("BASE-Jumping");
                    add("Skydiving");
                    add("Speed-Flying");
                }}
        );
        customer.setPhones(createPhones());
        customer.setAddresses(createAddresses());
        return customer;
    }

    public static List<Phone> createPhones(){
        return createPhones(1);
    }

    public static List<Phone> createPhones(int numberOfPhones){
        List<Phone> phones = new ArrayList<Phone>();

        for(int i=0; i< numberOfPhones; i++){
            int areaCode = getRandomInt(i, 999);
            int phone = getRandomInt(i, 9999999);

            phones.add(
                    new Phone(
                            PhoneType.WORK,
                            String.format("%03d", areaCode),
                            String.format("%07d", phone)
                    )
            );
        }
        return phones;
    }

    protected static int getRandomInt(int min, int max){
        return (int) ((max - min + 1) * random.nextDouble() + 1);
    }

    public static Map<String, Address> createAddresses(){
        final int pk = getRandomInt(1, 999);
        return new HashMap<String, Address>(){{
            put(String.format("%03d", pk),
                new Address(
                        AddressType.RESIDENTIAL,
                        String.valueOf(pk + Math.random()),
                        String.valueOf(pk + Math.random()),
                        "CA",
                        "94114"
                )
            );
        }};
    }

    /*
     * Does not work.
     * @see http://www.baselogic.com/blog/development/java-javaee-j2ee/no-subclass-matches-this-class-for-this-aggregate-mapping-with-inheritance
    public static Map<String, Address> createAddresses() {
        final int pk = getRandomInt(1, 999);
        return new HashMap<String, Address>() {{
            put(String.format("%03d", pk),
                    new Address(){{
                        setType(AddressType.RESIDENTIAL);
                        setStreet("123 Main Street");
                        setCity("San Francisco");
                        setState("CA");
                        setPostCode("91335");
                        setProvince("");
                    }}
            );
        }};
    }*/

}
