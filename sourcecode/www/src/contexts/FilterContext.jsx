import { createContext, useContext } from "react";
import useArray from "../hooks/useArray";
import { H5P_TYPE, LICENSE } from "../constants/resourceFilters";

const FilterContext = createContext();

export const FilterContextProvider = ({ children }) => {
    const openFilters = useArray([H5P_TYPE, LICENSE]);

    return (
        <FilterContext.Provider value={{ openFilters }}>
            {children}
        </FilterContext.Provider>
    );
};

export const useOpenFilters = () => {
    const { openFilters } = useContext(FilterContext);

    return openFilters;
};
