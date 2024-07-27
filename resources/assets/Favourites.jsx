import Pagination from '@/Components/Pagination';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function Index(props) {
    const {links} = usePage().props;
    const [userLinks, setUserLinks] = useState(props.links.data);

    function toggleFavourites(event, sourceLinkId) {
        event.preventDefault();
        axios.post(route('news.toggle-favourite'), {
            sourceLinkId: sourceLinkId
        }).then((response) => {
            setUserLinks(prevUserLinks =>
                prevUserLinks.map(link =>
                    link.source_link_id === sourceLinkId
                        ? { ...link, favorited: link.favorited === 1 ? 0 : 1 }
                        : link
                )
            );
        }).catch((error) => {
            console.log(error);
        });
    }

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Favourites</h2>}
        >
            <Head title="Favourites" />
            <div className={"max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8"}>
                {userLinks.map((link, index) => {
                    return (
                        <div>
                            <div className="text-gray-800 dark:text-gray-200 flex flex-wrap py-4 border-b border-gray-100 dark:border-gray-700">
                                    <div className="text-[grey] mr-3 text-4xl">{index}</div>
                                    <div>
                                        <h2 className="text-[#007bff] text-2xl">
                                            <a href={link.source_link} target="_blank">{link.source_title} ({link.source_name})</a>
                                        </h2>
                                        <p className="text-[grey]">
                                            <span>Added { link.source_date }</span> - <a href="#" onClick={(event) => toggleFavourites(event, link.source_link_id)}>{!link.favorited ? "Add to Favourites" : "Remove from Favourites"}</a>
                                        </p>
                                    </div>
                            </div>
                        </div>
                    )
                })}

                <Pagination class="mt-6" total={links.total} links={links.links} />
            </div>
        </AuthenticatedLayout>
    )
}
