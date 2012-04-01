/**
 * External Groovy Configuration
 */

example{
    foo = "default_foo"
    bar = "default_bar"
    baz = 1234L
    reloadable = false
}


// Built-in groovy support:
environments {
    development{
        example{
            foo = "development_foo"
            bar = "development_bar"
            baz = 5678L
            reloadable = true
        }
    }
}

// Case statement version:
/*switch (environment) {
    case 'development':
        example{
            foo = "development_foo"
            bar = "development_bar"
            baz = 5678L
            reloadable = true
        }
        break
    case 'test':
        example{
            foo = "test_foo"
            bar = "test_bar"
            baz = 91011L
            reloadable = true
        }
        break
    default:
        baseUrl = "localhost/"
}*/
