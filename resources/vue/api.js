import CategoryService from "@/services/CategoryService";
import CommentService from "@/services/CommentService";
import CountryService from "@/services/CountryService";
import ReportService from "@/services/ReportService";
import UserService from "@/services/UserService";
import ConferenceService from "@/services/ConferenceService";
import ZoomService from "@/services/ZoomService";
import PlanService from "@/services/PlanService";

export const api = {
    category: new CategoryService(),
    comment: new CommentService(),
    conference: new ConferenceService(),
    country: new CountryService(),
    report: new ReportService(),
    user: new UserService(),
    zoom: new ZoomService(),
    plan: new PlanService(),
    setToken: (token) => {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    removeToken: () => {
        delete axios.defaults.headers.common['Authorization'];
    }
}
