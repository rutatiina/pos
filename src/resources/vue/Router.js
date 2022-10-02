
const Index = () => import('./components/l-limitless-bs4/Index');
const Form = () => import('./components/l-limitless-bs4/Form');
const Show = () => import('./components/l-limitless-bs4/Show');
const SideBarLeft = () => import('./components/l-limitless-bs4/SideBarLeft');
const SideBarRight = () => import('./components/l-limitless-bs4/SideBarRight');

const routes = [

    {
        path: '/pos',
        components: {
            default: Index,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Bills',
            metaTags: [
                {
                    name: 'description',
                    content: 'Recurring Bills'
                },
                {
                    property: 'og:description',
                    content: 'Recurring Bills'
                }
            ]
        }
    },
    {
        path: '/pos/create',
        components: {
            default: Form,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Bill :: Create',
            metaTags: [
                {
                    name: 'description',
                    content: 'Create Recurring Bill'
                },
                {
                    property: 'og:description',
                    content: 'Create Recurring Bill'
                }
            ]
        }
    },
    {
        path: '/pos/:id',
        components: {
            default: Show,
            'sidebar-left': SideBarLeft,
            'sidebar-right': SideBarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Bill',
            metaTags: [
                {
                    name: 'description',
                    content: 'Recurring Bill'
                },
                {
                    property: 'og:description',
                    content: 'Recurring Bill'
                }
            ]
        }
    },
    {
        path: '/pos/:id/copy',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Bill :: Copy',
            metaTags: [
                {
                    name: 'description',
                    content: 'Copy Recurring Bill'
                },
                {
                    property: 'og:description',
                    content: 'Copy Recurring Bill'
                }
            ]
        }
    },
    {
        path: '/pos/:id/edit',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Bill :: Edit',
            metaTags: [
                {
                    name: 'description',
                    content: 'Edit Recurring Bill'
                },
                {
                    property: 'og:description',
                    content: 'Edit Recurring Bill'
                }
            ]
        }
    }

]

export default routes
