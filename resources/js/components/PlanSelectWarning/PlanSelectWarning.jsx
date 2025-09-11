import { Banner, Button, Link } from "@shopify/polaris";
import { getRedirectUrl } from "../../utils/utils";

const PlanSelectWarning = () => {
    return (
        <Banner tone="warning">
            <p>
                Your app access is limited until you choose a subscription plan.{" "}
                <Button url={getRedirectUrl("/plans")} variant="plain">Select a plan</Button> to continue.
            </p>
        </Banner>
    );
};

export default PlanSelectWarning;
